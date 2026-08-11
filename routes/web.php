<?php

use App\Http\Controllers\AdminNotificationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingExportController;
use App\Models\Transaction;
use App\Models\WebsiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TourController;

// ─── Diagnostic Health Check (no middleware, no session) ──────────────────────
// Remove this route once production is stable.
Route::withoutMiddleware([])->get('/health-check', function () {
    $checks = [];

    // 1. PHP / Framework boot
    $checks['php_version'] = PHP_VERSION;
    $checks['laravel_version'] = app()->version();
    $checks['app_env'] = config('app.env');
    $checks['app_key_set'] = !empty(config('app.key'));
    $checks['app_key_length'] = strlen(config('app.key') ?? '');

    // 2. Database
    try {
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        $checks['database'] = 'connected';
        $checks['db_name'] = \Illuminate\Support\Facades\DB::connection()->getDatabaseName();
        $checks['sessions_table'] = \Illuminate\Support\Facades\DB::connection()->getSchemaBuilder()->hasTable('sessions') ? 'exists' : 'MISSING';
        $checks['cache_table'] = \Illuminate\Support\Facades\DB::connection()->getSchemaBuilder()->hasTable('cache') ? 'exists' : 'MISSING';
    } catch (\Throwable $e) {
        $checks['database'] = 'ERROR: ' . $e->getMessage();
    }

    // 3. Storage / symlink
    $checks['storage_link'] = is_link(public_path('storage')) ? 'ok' : 'missing';

    // 4. Cache driver
    $checks['cache_driver'] = config('cache.default');
    try {
        \Illuminate\Support\Facades\Cache::put('_health_test', 1, 5);
        $checks['cache_write'] = \Illuminate\Support\Facades\Cache::get('_health_test') === 1 ? 'ok' : 'read_failed';
    } catch (\Throwable $e) {
        $checks['cache_write'] = 'ERROR: ' . $e->getMessage();
    }

    // 5. Mail config
    $checks['mail_mailer'] = config('mail.default');
    $checks['sendgrid_key_set'] = !empty(config('mail.mailers.sendgrid.api_key'));

    // 6. Firebase credentials path
    $checks['firebase_credentials_path'] = config('firebase.credentials');
    $checks['firebase_file_exists'] = file_exists(config('firebase.credentials') ?? '') ? 'yes' : 'no/path-missing';

    return response()->json([
        'status' => collect($checks)->filter(fn ($v) => str_starts_with((string) $v, 'ERROR') || $v === 'MISSING')->isEmpty() ? 'healthy' : 'degraded',
        'checks' => $checks,
        'timestamp' => now()->toISOString(),
    ]);
})->name('health.check');
// ─────────────────────────────────────────────────────────────────────────────

$renderWebsitePage = function (string $page, string $view) {
    class_exists(\App\Models\WebsiteSetting::class);

    $settingsData = \Illuminate\Support\Facades\Cache::remember('website_settings:page:' . $page, now()->addHour(), function () use ($page) {
        try {
            return WebsiteSetting::firstWhere('page', $page)?->toArray() ?? [];
        } catch (\Throwable $e) {
            return [];
        }
    });

    $normalizePath = function (?string $path): ?string {
        if (empty($path)) {
            return null;
        }

        return storage_asset_path($path) ?: null;
    };

    $normalizeCards = function (array $cards) use ($normalizePath): array {
        return array_map(function ($card) use ($normalizePath) {
            if (! is_array($card)) {
                return $card;
            }

            if (isset($card['image'])) {
                $card['image'] = $normalizePath((string) $card['image']) ?: $card['image'];
            }

            return $card;
        }, $cards);
    };

    $settingsData['hero_images'] = array_values(array_filter(array_map($normalizePath, $settingsData['hero_images'] ?? [])));
    $settingsData['booking_cards'] = $normalizeCards($settingsData['booking_cards'] ?? []);
    $settingsData['content']['booking_cards'] = $normalizeCards($settingsData['content']['booking_cards'] ?? []);
    $settingsData['header_data']['logo'] = $normalizePath($settingsData['header_data']['logo'] ?? null) ?: ($settingsData['header_data']['logo'] ?? null);
    $settingsData['footer_data']['logo'] = $normalizePath($settingsData['footer_data']['logo'] ?? null) ?: ($settingsData['footer_data']['logo'] ?? null);

    return view($view, [
        'pageSettings' => (object) ($settingsData ?? []),
        'pageContent' => $settingsData['content'] ?? [],
        'heroImages' => collect($settingsData['hero_images'] ?? []),
        'bookingCards' => collect($settingsData['booking_cards'] ?? $settingsData['content']['booking_cards'] ?? []),
        'activeRoutes' => \Illuminate\Support\Facades\Cache::remember('web:activeRoutes', now()->addMinutes(15), function () {
            try {
                return \App\Models\FerryRoute::query()
                    ->active()
                    ->whereHas('schedules', fn ($q) => $q->active()->where('departure_time', '>=', \Carbon\Carbon::today()))
                    ->with(['vehicle', 'schedules' => fn ($q) => $q->active()->where('departure_time', '>=', \Carbon\Carbon::today())])
                    ->get()
                    ->map(function ($route) {
                        $operator = normalize_operator_name($route->operator ?: ($route->vehicle?->operator ?? '')) ?: '';

                        return [
                            'origin' => $route->origin,
                            'destination' => $route->destination,
                            'mode' => $route->mode,
                            'operator' => $operator,
                            'dates' => $route->schedules->pluck('departure_time')->map(fn ($dt) => \Carbon\Carbon::parse($dt)->format('Y-m-d'))->unique()->values()->all(),
                        ];
                    })
                    ->groupBy(fn ($item) => implode('|', [$item['origin'], $item['destination'], $item['mode'], $item['operator']]))
                    ->map(function ($group) {
                        $first = $group->first();
                        $first['dates'] = $group->pluck('dates')->flatten()->unique()->sort()->values()->all();
                        return $first;
                    })
                    ->values()
                    ->all();
            } catch (\Throwable $e) {
                return [];
            }
        }),
        'vehicleRates' => \Illuminate\Support\Facades\Cache::remember('web:vehicleRates', now()->addMinutes(30), function () {
            try {
                return \App\Models\VehicleRate::query()->where('is_active', true)->orderBy('sort_order')->get()->map(fn ($r) => [
                    'id' => $r->id,
                    'name' => $r->name,
                    'price' => (float) $r->price,
                ])->all();
            } catch (\Throwable $e) { return []; }
        }),
        'vehicleBrands' => \Illuminate\Support\Facades\Cache::remember('web:vehicleBrands', now()->addMinutes(30), function () {
            try {
                return \App\Models\VehicleBrand::query()->where('is_active', true)->orderBy('sort_order')->with('models')->get()->map(fn ($b) => [
                    'id' => $b->id,
                    'name' => $b->name,
                    'models' => $b->models->where('is_active', true)->sortBy('sort_order')->map(fn ($m) => [
                        'id' => $m->id,
                        'name' => $m->name,
                        'price' => (float) $m->price,
                    ])->values()->all(),
                ])->all();
            } catch (\Throwable $e) { return []; }
        }),
    ]);
};

Route::get('/', function () use ($renderWebsitePage) {
    return $renderWebsitePage('home', 'home');
})->name('home');

Route::get('/tours', [TourController::class, 'index'])->name('tours.index');
Route::get('/tours/{tour}', [TourController::class, 'show'])->name('tours.show');

Route::post('/booking/draft/cancel', function (Request $request) {
    $request->session()->forget('booking_draft');

    return redirect()->route('home');
})->name('booking.draft.cancel');

Route::redirect('/book', '/book/new')->name('book');
Route::view('/book/new', 'book')->name('book.new');
Route::view('/book/status', 'book-status')->name('book.status');
Route::get('/booking/reschedule/{transaction_number}', function (string $transaction_number) {
    return view('booking-reschedule', ['transaction_number' => $transaction_number]);
})->name('booking.reschedule');

Route::get('/about', function () use ($renderWebsitePage) {
    return $renderWebsitePage('about', 'about');
})->name('about');



Route::get('/services', function () use ($renderWebsitePage) {
    return $renderWebsitePage('services', 'services');
})->name('services');

Route::get('/tour-package', function () use ($renderWebsitePage) {
    return $renderWebsitePage('tour_package', 'tour-package');
})->name('tour-package');

Route::get('/contact-us', function () use ($renderWebsitePage) {
    return $renderWebsitePage('contact_us', 'contact');
})->name('contact');

Route::post('/contact-us', function (Illuminate\Http\Request $request) {
    $data = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'subject' => 'nullable|string|max:255',
        'message' => 'required|string',
    ]);

    App\Models\Inquiry::create($data);

    return response()->json(['message' => 'Inquiry received']);
})->name('contact.submit');

Route::get('/download', function () use ($renderWebsitePage) {
    return $renderWebsitePage('download', 'download');
})->name('download');

Route::get('/faqs', function () use ($renderWebsitePage) {
    return $renderWebsitePage('faqs', 'faqs');
})->name('faqs');

Route::get('/schedules', function (\Illuminate\Http\Request $request) {
    $startDate = $request->query('start_date', \Carbon\Carbon::today()->format('Y-m-d'));
    $endDate = $request->query('end_date', \Carbon\Carbon::today()->addDays(6)->format('Y-m-d'));

    $routes = \Illuminate\Support\Facades\Cache::remember('web:schedules:' . $startDate . ':' . $endDate, now()->addMinutes(5), function () use ($startDate, $endDate) {
        $routesData = App\Models\FerryRoute::with([
            'schedules' => function ($query) use ($startDate, $endDate) {
            $query->active()
                  ->where('departure_time', '>=',
                      // When viewing today, exclude schedules whose departure has already passed (to the second)
                      \Carbon\Carbon::parse($startDate)->isToday()
                          ? \Carbon\Carbon::now()
                          : \Carbon\Carbon::parse($startDate)->startOfDay()
                  )
                  ->where('departure_time', '<=', \Carbon\Carbon::parse($endDate)->endOfDay())
                  ->orderBy('departure_time');
            },
            'schedules.scheduleAccommodations',
            'schedules.transportClasses',
        ])->where('is_active', true)->orderBy('origin')->orderBy('destination')->get();
        
        return $routesData->filter(fn ($route) => $route->schedules->isNotEmpty());
    });


    class_exists(\App\Models\WebsiteSetting::class);
    $settingsData = \Illuminate\Support\Facades\Cache::remember('website_settings:page:schedules', now()->addHour(), function () {
        try {
            return WebsiteSetting::firstWhere('page', 'schedules')?->toArray() ?? [];
        } catch (\Throwable $e) {
            return [];
        }
    });
    $pageSettings = (object) ($settingsData ?? []);
    $pageContent = $settingsData['content'] ?? [];

    $activeTab = $request->query('tab', 'ferry');
    if (!in_array($activeTab, ['ferry', 'airlines'])) {
        $activeTab = 'ferry';
    }

    return view('schedules', compact('routes', 'startDate', 'endDate', 'pageSettings', 'pageContent', 'activeTab'));
})->name('schedules');

Route::get('/payment/{transaction}', function (Transaction $transaction) {
    $transaction->load('booking');

    return view('payment', [
        'transaction' => $transaction,
        'qrCodePath' => App\Models\PaymentSetting::current()->qr_code_path,
    ]);
})->name('payment.show');

Route::get('/ticket/download/{transaction_number}', function ($transaction_number) {
    $booking = \App\Models\Booking::where('transaction_number', $transaction_number)
        ->with(['passengers.discount', 'schedule.ferryRoute', 'returnSchedule', 'transaction', 'accommodations', 'transportClasses'])
        ->firstOrFail();

    if ($booking->transaction && !empty($booking->transaction->confirmation_pdf)) {
        $pdfPath = is_string($booking->transaction->confirmation_pdf) 
            ? $booking->transaction->confirmation_pdf 
            : null;
        
        if ($pdfPath) {
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($pdfPath)) {
                return response()->file(\Illuminate\Support\Facades\Storage::disk('public')->path($pdfPath), [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="Payment_Acknowledgement.pdf"',
                ]);
            }
        }
    }

    $receiptDir = storage_path('app/receipts');
    $path = $receiptDir . '/receipt-' . $booking->transaction_number . '.pdf';

    if (! file_exists($path)) {
        try {
            if (! is_dir($receiptDir)) {
                mkdir($receiptDir, 0755, true);
            }
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.receipt', ['booking' => $booking]);
            $pdf->setPaper('a4');
            $pdf->save($path);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('PDF generation failed: ' . $e->getMessage());
            return response()->view('pdf.receipt', ['booking' => $booking]);
        }
    }

    return response()->file($path, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'inline; filename="Payment_Acknowledgement.pdf"',
    ]);
})->name('ticket.download');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth')->name('dashboard');

Route::get('/flutter-app', function () {
    return view('flutter');
})->name('flutter.app');

// Booking Export Routes (Admin only)
Route::middleware(['auth:admin,web', 'admin'])->group(function () {
    Route::get('/admin/bookings/export/pdf', [BookingExportController::class, 'exportPdf'])->name('bookings.export.pdf')->middleware('staff.permission:bookings');
    Route::get('/admin/bookings/export/csv', [BookingExportController::class, 'exportCsv'])->name('bookings.export.csv')->middleware('staff.permission:bookings');
    Route::get('/admin/bookings/export/print', [BookingExportController::class, 'exportPrint'])->name('bookings.export.print')->middleware('staff.permission:bookings');
    Route::get('/admin/notifications/dropdown', [AdminNotificationController::class, 'dropdown']);
    Route::get('/admin/notifications/api/list', [AdminNotificationController::class, 'list']);
    Route::post('/admin/notifications/api/mark-read', [AdminNotificationController::class, 'markRead']);
    Route::post('/admin/notifications/api/mark-unread', [AdminNotificationController::class, 'markUnread']);
    Route::delete('/admin/notifications/api', [AdminNotificationController::class, 'destroy']);
});

Route::get('/db-test', function () {
    try {
        $pdo = DB::connection()->getPdo();
        $dbName = DB::connection()->getDatabaseName();
        $tables = DB::select('SHOW TABLES');
        $schedulesCount = App\Models\Schedule::count();
        $routesCount = App\Models\FerryRoute::count();

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully connected to the database.',
            'database' => $dbName,
            'tables_count' => count($tables),
            'schedules_count' => $schedulesCount,
            'routes_count' => $routesCount,
            'host' => config('database.connections.mysql.host'),
            'port' => config('database.connections.mysql.port'),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to connect to the database: ' . $e->getMessage(),
            'host' => config('database.connections.mysql.host'),
            'port' => config('database.connections.mysql.port'),
        ], 500);
    }
})->middleware(['auth:admin,web', 'admin']);

