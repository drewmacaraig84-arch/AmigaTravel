<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Support\ReportingService;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Livewire\Attributes\Url;
use App\Models\Booking;

class StaffPerformance extends Page implements HasForms
{
    use InteractsWithForms;

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user instanceof User && $user->hasAdminPermission('staff_performance');
    }

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Reports';
    protected static ?int $navigationSort = 20;
    protected static ?string $navigationLabel = 'Staff Performance';
    protected static ?string $title = 'Staff Performance Reports';
    protected static string $view = 'filament.pages.staff-performance';

    #[Url]
    public ?string $filterDate = null;

    public \Illuminate\Support\Collection $staffStats;

    public function mount(): void
    {
        if (!$this->filterDate) {
            $this->filterDate = now()->format('Y-m-d');
        }
        $this->form->fill(['filterDate' => $this->filterDate]);
        $this->loadStats();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('filterDate')
                    ->label('Filter by Date')
                    ->native(false)
                    ->live()
                    ->afterStateUpdated(fn () => $this->loadStats()),
            ]);
    }

    public function loadStats(): void
    {
        $service = app(ReportingService::class);
        $this->staffStats = $service->getStaffStats($this->filterDate);
    }
    
    public function getStaffBookings(int $staffId)
    {
        $query = Booking::where('verified_by_user_id', $staffId);
        
        if ($this->filterDate) {
            $query->whereDate('verified_at', $this->filterDate);
        }
        
        return $query->with('transaction')->latest('verified_at')->get();
    }
}
