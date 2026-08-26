<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\GraciaUserBalance;
use App\Models\GraciaPointLedger;
use App\Models\Booking;
use App\Models\UserLoginHistory;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'phone', 'password', 'role', 'is_staff', 'is_admin', 'admin_permissions', 'api_token', 'referral_code', 'referred_by', 'referral_redeemed', 'welcome_bonus_claimed'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    public const PERMISSION_GROUPS = [
        'Management' => [
            'promotional_tickets' => 'Promotional Tickets',
            'tour_packages' => 'Tour Packages',
        ],
        'Settings' => [
            'app_notifications' => 'App Notifications',
            'website_settings' => 'Website Settings',
            'payment_settings' => 'Payment Settings',
            'proofs' => 'Proofs',
            'gracia_rules' => 'Gracia Rules',
        ],
        'Administration' => [
            'staff_accounts' => 'Staff Accounts',
            'inquiries' => 'Inquiries',
            'mobile_apk_users' => 'Mobile APK Users',
        ],
        'Reports' => [
            'overall_reports' => 'Overall Reports',
            'staff_performance' => 'Staff Performance',
        ],
        'Travel' => [
            'hotels' => 'Hotels',
            'travel_routes' => 'Travel Routes',
            'vouchers' => 'Vouchers',
            'discounts' => 'Discounts',
            'schedules' => 'Schedules',
            'ferry_airline' => 'Ferry & Airline',
            'service_cancellations' => 'Service Cancellations',
        ],
        'Bookings' => [
            'bookings' => 'Bookings',
            'transactions' => 'Transactions',
            'receipts' => 'Receipts',
        ],
        'Airline' => [
            'airline_seats' => 'Airline Seats',
            'airline_baggage' => 'Airline Baggage',
        ],
        'Ferry' => [
            'vehicle_rates' => 'Vehicle Rates',
        ],
    ];

    public const ADMIN_PERMISSIONS = [
        'promotional_tickets' => 'Promotional Tickets',
        'tour_packages' => 'Tour Packages',
        'app_notifications' => 'App Notifications',
        'website_settings' => 'Website Settings',
        'payment_settings' => 'Payment Settings',
        'proofs' => 'Proofs',
        'gracia_rules' => 'Gracia Rules',
        'staff_accounts' => 'Staff Accounts',
        'inquiries' => 'Inquiries',
        'mobile_apk_users' => 'Mobile APK Users',
        'overall_reports' => 'Overall Reports',
        'staff_performance' => 'Staff Performance',
        'hotels' => 'Hotels',
        'travel_routes' => 'Travel Routes',
        'vouchers' => 'Vouchers',
        'discounts' => 'Discounts',
        'schedules' => 'Schedules',
        'ferry_airline' => 'Ferry & Airline',
        'service_cancellations' => 'Service Cancellations',
        'bookings' => 'Bookings',
        'transactions' => 'Transactions',
        'receipts' => 'Receipts',
        'airline_seats' => 'Airline Seats',
        'airline_baggage' => 'Airline Baggage',
        'vehicle_rates' => 'Vehicle Rates',
    ];

    public const LEGACY_PERMISSION_MAP = [
        'manage_accommodations' => 'hotels',
        'manage_transport_classes' => 'airline_seats',
        'manage_vehicle_rates' => 'vehicle_rates',
        'manage_bookings' => 'bookings',
        'manage_discounts' => 'discounts',
        'manage_routes' => 'travel_routes',
        'manage_schedules' => 'schedules',
        'manage_transactions' => 'transactions',
        'manage_users' => 'staff_accounts',
        'manage_inquiries' => 'inquiries',
        'manage_payment_settings' => 'payment_settings',
        'manage_website_settings' => 'website_settings',
        'manage_proofs' => 'proofs',
    ];

    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_staff' => 'boolean',
        'is_admin' => 'boolean',
        'admin_permissions' => 'array',
    ];

    public function isSuperAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    public function isStaff(): bool
    {
        return (bool) $this->is_staff || (bool) $this->is_admin;
    }

    public static function getPermissionGroups(): array
    {
        return self::PERMISSION_GROUPS;
    }

    public static function normalizePermissionKey(string $permission): string
    {
        return self::LEGACY_PERMISSION_MAP[$permission] ?? $permission;
    }

    public static function normalizePermissions(?array $permissions): array
    {
        $normalized = array_map(
            fn (mixed $permission): string => self::normalizePermissionKey((string) $permission),
            $permissions ?? []
        );

        $normalized = array_values(array_filter($normalized, fn (string $permission): bool => filled($permission)));

        return array_values(array_unique($normalized));
    }

    public function getAdminPermissionKeys(): array
    {
        return self::normalizePermissions($this->admin_permissions ?? []);
    }

    public function hasAdminPermission(string $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return in_array(self::normalizePermissionKey($permission), $this->getAdminPermissionKeys(), true);
    }

    public function canAccessFeature(string $permission): bool
    {
        if ($permission === 'dashboard') {
            return $this->isStaff();
        }

        return $this->isSuperAdmin() || $this->hasAdminPermission($permission);
    }

    public function hasAnyAdminPermission(): bool
    {
        return $this->isSuperAdmin() || ! empty($this->getAdminPermissionKeys());
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if (request()?->routeIs('*.auth.*')) {
            return true;
        }

        return $this->isStaff();
    }

    public function loginHistories(): HasMany
    {
        return $this->hasMany(UserLoginHistory::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'client_email', 'email');
    }

    public function verifiedBookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'verified_by_user_id');
    }

    public function graciaBalance(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(GraciaUserBalance::class);
    }

    public function graciaPointLedgers(): HasMany
    {
        return $this->hasMany(GraciaPointLedger::class);
    }
}
