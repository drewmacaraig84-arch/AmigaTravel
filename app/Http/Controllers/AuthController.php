<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function requestEmailVerification(Request $request)
    {
        $validated = $request->validate(['email' => 'required|email']);
        $email = strtolower(trim($validated['email']));

        if (! Booking::where('client_email', '=', $email, 'and')->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No booking was found for this email address.',
            ], 404);
        }

        $code = (string) random_int(100000, 999999);
        Cache::put('booking_lookup_otp:' . $email, $code, now()->addMinutes(10));

        Mail::raw("Your Amiga Gracia booking verification code is {$code}. It expires in 10 minutes.", function ($message) use ($email): void {
            $message->to($email)->subject('Amiga Gracia booking verification code');
        });

        return response()->json([
            'status' => 'success',
            'message' => 'A verification code was sent to your email.',
        ]);
    }

    public function verifyEmail(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:6',
        ]);
        $email = strtolower(trim($validated['email']));
        $cacheKey = 'booking_lookup_otp:' . $email;

        if (! hash_equals((string) Cache::get($cacheKey, ''), $validated['code'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'The verification code is invalid or expired.',
            ], 422);
        }

        Cache::forget($cacheKey);
        User::where('email', '=', $email, 'and')->update(['email_verified_at' => now()]);
        $lookupToken = Str::random(80);
        Cache::put('booking_lookup_token:' . hash('sha256', $lookupToken), $email, now()->addDays(30));

        return response()->json([
            'status' => 'success',
            'message' => 'Email verified successfully.',
            'email' => $email,
            'lookup_token' => $lookupToken,
        ]);
    }

    private function issueLookupToken(string $email): string
    {
        $lookupToken = Str::random(80);
        Cache::put('booking_lookup_token:' . hash('sha256', $lookupToken), strtolower($email), now()->addDays(30));

        return $lookupToken;
    }

    public function showLogin(): View
    {
        return view('login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            $this->logUserLogin(
                null,
                'web_login',
                $request,
                false,
                'Failed web login attempt.'
            );

            $userExists = User::where('email', $credentials['email'])->exists();
            $errorMessage = $userExists ? 'Incorrect password.' : 'Incorrect email address.';

            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => $errorMessage]);
        }

        $user = Auth::user();
        if (empty($user->referral_code)) {
            $user->referral_code = strtoupper(Str::random(8));
            $user->save();
        }

        $request->session()->regenerate();

        $this->logUserLogin(
            Auth::user(),
            'web_login',
            $request,
            true,
            'Successful web login.'
        );

        $this->backfillBookingUserIds(Auth::user());

        return redirect()->intended(route('dashboard'));
    }

    protected function logUserLogin(?User $user, string $type, Request $request, bool $success, string $description = null): void
    {
        \App\Models\UserLoginHistory::create([
            'user_id' => $user?->id,
            'email' => $request->input('email'),
            'login_type' => $type,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'success' => $success,
            'description' => $description,
            'metadata' => [
                'remember' => $request->boolean('remember'),
            ],
        ]);
    }

    protected function backfillBookingUserIds(User $user): void
    {
        $bookings = Booking::whereNull('user_id')
            ->where('client_email', $user->email)
            ->get();

        foreach ($bookings as $booking) {
            $booking->update(['user_id' => $user->id]);

            if ($booking->status === 'confirmed') {
                app(\App\Services\GraciaPointsService::class)->awardPointsForBooking($booking);
            }
        }
    }

    public function showRegister(): View
    {
        return view('register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);
        $validated['password'] = \Illuminate\Support\Facades\Hash::make($validated['password']);
        $user = User::create($validated);

        Auth::login($user);

        $request->session()->regenerate();

        $this->logUserLogin(
            $user,
            'web_register',
            $request,
            true,
            'Successful web registration.'
        );

        $this->backfillBookingUserIds($user);

        return redirect()->route('dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('book');
    }

    public function apiLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (! Auth::attempt($credentials)) {
            $this->logUserLogin(
                null,
                'api_login',
                $request,
                false,
                'Failed API login attempt.'
            );

            $userExists = User::where('email', $credentials['email'])->exists();
            $errorMessage = $userExists ? 'Incorrect password.' : 'Incorrect email address.';

            return response()->json([
                'message' => $errorMessage
            ], 422);
        }

        $user = Auth::user();

        if (!$user->is_app_user) {
            $this->logUserLogin(
                $user,
                'api_login',
                $request,
                false,
                'Denied: Account is not an app user.'
            );
            return response()->json([
                'message' => 'This account isn\'t registered on the app. Try registering first.'
            ], 403);
        }

        if (empty($user->referral_code)) {
            $user->referral_code = strtoupper(Str::random(8));
            $user->save();
        }

        // Issue a fresh Sanctum token (scoped, revocable)
        // Delete previous tokens to avoid accumulation (one active session per user)
        $user->tokens()->where('name', 'api-access')->delete();
        $sanctumToken = $user->createToken('api-access')->plainTextToken;

        // Also keep the legacy api_token populated for backward compat with older Flutter builds
        $user->api_token = Str::random(80);
        $user->save();

        $this->logUserLogin(
            $user,
            'api_login',
            $request,
            true,
            'Successful API login.'
        );

        $this->backfillBookingUserIds($user);

        return response()->json([
            'status' => 'success',
            'user' => [
                'name'  => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? '',
                'referral_code' => $user->referral_code,
            ],
            // Legacy token — kept for backward compat
            'token'         => $user->api_token,
            // Sanctum token — use this in new Flutter builds
            'sanctum_token' => $sanctumToken,
            'lookup_token'  => $this->issueLookupToken($user->email),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->guard('api')->user();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        $user->name = $validated['name'];
        if (isset($validated['phone'])) {
            $user->phone = $validated['phone'];
        }
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Profile updated successfully',
            'user' => [
                'name'  => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? '',
                'referral_code' => $user->referral_code,
            ],
        ]);
    }

    public function apiRegister(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                function ($attribute, $value, $fail) {
                    $user = User::where('email', $value)->first();
                    if ($user && $user->is_app_user) {
                        $fail('The email has already been taken.');
                    }
                },
            ],
            'password' => 'required|string|min:8',
        ]);

        $legacyToken = Str::random(80);
        $validated['password']  = \Illuminate\Support\Facades\Hash::make($validated['password']);
        $validated['api_token'] = $legacyToken;
        $validated['is_app_user'] = true;

        $user = User::where('email', $validated['email'])->first();
        if ($user) {
            $user->update($validated);
        } else {
            $user = User::create($validated);
        }

        // Issue Sanctum token for new users
        $sanctumToken = $user->createToken('api-access')->plainTextToken;

        $this->logUserLogin(
            $user,
            'api_register',
            $request,
            true,
            'Successful API registration.'
        );

        $this->backfillBookingUserIds($user);

        return response()->json([
            'status' => 'success',
            'user' => [
                'name'  => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? '',
                'referral_code' => $user->referral_code,
            ],
            'token'         => $legacyToken,
            'sanctum_token' => $sanctumToken,
            'lookup_token'  => $this->issueLookupToken($user->email),
        ]);
    }

    /**
     * Step 1 of OTP-gated registration: validate inputs, cache pending data, send OTP email.
     */
    public function requestRegisterOtp(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => [
                'required',
                'email',
                'max:255',
                function ($attribute, $value, $fail) {
                    $user = User::where('email', $value)->first();
                    if ($user && $user->is_app_user) {
                        $fail('The email has already been taken.');
                    }
                },
            ],
            'password' => 'required|string|min:8',
            'referral_code' => 'nullable|string',
        ]);

        $email = strtolower(trim($validated['email']));
        $otp   = (string) random_int(100000, 999999);

        // Cache the pending registration data for 10 minutes
        Cache::put('pending_register:' . $email, [
            'name'     => $validated['name'],
            'email'    => $email,
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
            'referral_code' => $validated['referral_code'] ?? null,
            'otp'      => $otp,
        ], now()->addMinutes(2));

        Mail::raw(
            "Hello {$validated['name']},\n\nYour Amiga Gracia registration verification code is: {$otp}\n\nThis code expires in 2 minutes. Do not share it with anyone.",
            function ($message) use ($email, $validated): void {
                $message->to($email)->subject('Amiga Gracia – Email Verification Code');
            }
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'A 6-digit verification code has been sent to your email.',
        ]);
    }

    public function resendRegisterOtp(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $email = strtolower(trim($validated['email']));
        $pending = Cache::get('pending_register:' . $email);

        if (! $pending) {
            return response()->json([
                'status' => 'error',
                'message' => 'No pending registration found. Please register again.',
            ], 422);
        }

        $otp = (string) random_int(100000, 999999);
        $pending['otp'] = $otp;
        Cache::put('pending_register:' . $email, $pending, now()->addMinutes(2));

        Mail::raw(
            "Hello {$pending['name']},\n\nYour new Amiga Gracia registration verification code is: {$otp}\n\nThis code expires in 2 minutes. Do not share it with anyone.",
            function ($message) use ($email): void {
                $message->to($email)->subject('Amiga Gracia – New Email Verification Code');
            }
        );

        return response()->json([
            'status' => 'success',
            'message' => 'A new 6-digit verification code has been sent to your email.',
        ]);
    }

    /**
     * Step 2 of OTP-gated registration: verify OTP and create the user account.
     */
    public function verifyRegisterOtp(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'otp'   => 'required|string|size:6',
        ]);

        $email   = strtolower(trim($validated['email']));
        $pending = Cache::get('pending_register:' . $email);

        if (! $pending) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Registration session expired. Please start over.',
            ], 422);
        }

        $otpInput = trim((string) $validated['otp']);

        if (! hash_equals((string) $pending['otp'], $otpInput)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid or expired verification code.',
            ], 422);
        }

        // Double-check uniqueness (race condition guard)
        $existingUser = User::where('email', $email)->first();
        if ($existingUser && $existingUser->is_app_user) {
            Cache::forget('pending_register:' . $email);
            return response()->json([
                'status'  => 'error',
                'message' => 'An account with this email already exists.',
            ], 422);
        }

        Cache::forget('pending_register:' . $email);

        $legacyToken = Str::random(80);
        
        $referredBy = null;
        if (!empty($pending['referral_code'])) {
            $referrer = User::where('referral_code', $pending['referral_code'])->first();
            if ($referrer) {
                $referredBy = $referrer->id;
            }
        }

        $user = User::where('email', $pending['email'])->first();
        if ($user) {
            $user->update([
                'name'              => $pending['name'],
                'password'          => $pending['password'],
                'api_token'         => $legacyToken,
                'email_verified_at' => now(),
                'referred_by'       => $referredBy,
                'is_app_user'       => true,
            ]);
            if (empty($user->referral_code)) {
                $user->update(['referral_code' => strtoupper(Str::random(8))]);
            }
        } else {
            $user = User::create([
                'name'              => $pending['name'],
                'email'             => $pending['email'],
                'password'          => $pending['password'],
                'api_token'         => $legacyToken,
                'email_verified_at' => now(),
                'referral_code'     => strtoupper(Str::random(8)),
                'referred_by'       => $referredBy,
                'is_app_user'       => true,
            ]);
        }

        // Process Rewards
        $settings = \App\Models\WebsiteSetting::where('page', 'referrals')->first();
        $rewards = $settings ? $settings->content : [];
        $referrerPoints = $rewards['referrer_points'] ?? 10;
        $refereePoints = $rewards['referee_points'] ?? 10;
        $welcomeBonus = $rewards['welcome_bonus'] ?? 50;

        if ($referredBy) {
            \App\Models\GraciaPointLedger::create([
                'user_id' => $referredBy,
                'points' => $referrerPoints,
                'entry_type' => 'earned',
                'reason' => 'Referral Bonus (Referred: ' . $user->email . ')',
                'idempotency_key' => 'ref_bonus_' . $referredBy . '_' . $user->id
            ]);
            $referrer->graciaBalance()->firstOrCreate(['user_id' => $referrer->id])->increment('current_balance', $referrerPoints);
            $referrer->graciaBalance()->firstOrCreate(['user_id' => $referrer->id])->increment('total_earned', $referrerPoints);

            \App\Models\GraciaPointLedger::create([
                'user_id' => $user->id,
                'points' => $refereePoints,
                'entry_type' => 'earned',
                'reason' => 'Referral Code Used',
                'idempotency_key' => 'ref_code_' . $user->id
            ]);
            $user->graciaBalance()->firstOrCreate(['user_id' => $user->id])->increment('current_balance', $refereePoints);
            $user->graciaBalance()->firstOrCreate(['user_id' => $user->id])->increment('total_earned', $refereePoints);
            
            $user->update(['referral_redeemed' => true]);
        }

        if (User::count() <= 100) {
            \App\Models\GraciaPointLedger::create([
                'user_id' => $user->id,
                'points' => $welcomeBonus,
                'entry_type' => 'earned',
                'reason' => 'First 100 Users Welcome Bonus',
                'idempotency_key' => 'welcome_bonus_' . $user->id
            ]);
            $user->graciaBalance()->firstOrCreate(['user_id' => $user->id])->increment('current_balance', $welcomeBonus);
            $user->graciaBalance()->firstOrCreate(['user_id' => $user->id])->increment('total_earned', $welcomeBonus);
            $user->update(['welcome_bonus_claimed' => true]);
        }

        // Issue Sanctum token for OTP-verified registrations
        $sanctumToken = $user->createToken('api-access')->plainTextToken;

        $this->logUserLogin(
            $user,
            'api_register',
            $request,
            true,
            'Successful OTP-verified API registration.'
        );

        $this->backfillBookingUserIds($user);

        return response()->json([
            'status'  => 'success',
            'message' => 'Account created successfully!',
            'user'    => [
                'name'  => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? '',
                'referral_code' => $user->referral_code,
            ],
            'token'        => $legacyToken,
            'sanctum_token'=> $sanctumToken,
            'lookup_token' => $this->issueLookupToken($user->email),
        ]);
    }

    /**
     * Step 1 of forgot password: send 6-digit OTP to user's registered email address.
     */
    public function requestPasswordResetOtp(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $email = strtolower(trim($validated['email']));
        $user  = User::where('email', $email)->first();

        if (! $user) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No account found with this email address.',
            ], 404);
        }

        $otp = (string) random_int(100000, 999999);

        Cache::put('password_reset_otp:' . $email, $otp, now()->addMinutes(15));

        Mail::raw(
            "Hello {$user->name},\n\nYour Amiga Gracia password reset code is: {$otp}\n\nThis code expires in 15 minutes. If you did not request a password reset, please ignore this email.",
            function ($message) use ($email): void {
                $message->to($email)->subject('Amiga Gracia – Password Reset Code');
            }
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'A 6-digit verification code has been sent to your email address.',
        ]);
    }

    /**
     * Step 2 of forgot password: verify OTP code and update password.
     */
    public function resetPasswordWithOtp(Request $request)
    {
        $validated = $request->validate([
            'email'    => 'required|email',
            'otp'      => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $email     = strtolower(trim($validated['email']));
        $cachedOtp = Cache::get('password_reset_otp:' . $email);

        if (! $cachedOtp || ! hash_equals((string) $cachedOtp, $validated['otp'])) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid or expired verification code. Please request a new code.',
            ], 400);
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            return response()->json([
                'status'  => 'error',
                'message' => 'User account not found.',
            ], 404);
        }

        $user->password = \Illuminate\Support\Facades\Hash::make($validated['password']);
        $user->save();

        Cache::forget('password_reset_otp:' . $email);

        return response()->json([
            'status'  => 'success',
            'message' => 'Your password has been reset successfully. You can now log in with your new password.',
        ]);
    }
}
