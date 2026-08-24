<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class PaymentSetting extends Model
{
    protected $fillable = [
        'web_admin_fee',
        'short_haul_web_admin_fee',
        'fee_per_accommodation',
        'transaction_fee',
        'short_haul_transaction_fee',
        'revalidation_fee',
        'qr_code_path',
        'proof_retention_days',
        'ferry_before_departure_surcharge_pct',
        'ferry_after_departure_surcharge_pct',
        'airline_before_departure_surcharge_pct',
        'rebook_ferry_before_departure_surcharge_pct',
        'rebook_ferry_after_departure_surcharge_pct',
        'rebook_airline_before_departure_surcharge_pct',
        'sla_voucher_enabled',
        'sla_voucher_hours',
        'sla_voucher_amount',
    ];

    protected $casts = [
        'web_admin_fee' => 'decimal:2',
        'short_haul_web_admin_fee' => 'decimal:2',
        'fee_per_accommodation' => 'decimal:2',
        'transaction_fee' => 'decimal:2',
        'short_haul_transaction_fee' => 'decimal:2',
        'revalidation_fee' => 'decimal:2',
        'proof_retention_days' => 'integer',
        'ferry_before_departure_surcharge_pct' => 'decimal:2',
        'ferry_after_departure_surcharge_pct' => 'decimal:2',
        'airline_before_departure_surcharge_pct' => 'decimal:2',
        'rebook_ferry_before_departure_surcharge_pct' => 'decimal:2',
        'rebook_ferry_after_departure_surcharge_pct' => 'decimal:2',
        'rebook_airline_before_departure_surcharge_pct' => 'decimal:2',
        'sla_voucher_enabled' => 'boolean',
        'sla_voucher_hours' => 'integer',
        'sla_voucher_amount' => 'decimal:2',
    ];

    /**
     * Get the web admin fee based on trip haul duration (< 5h is short haul).
     */
    public function getWebAdminFee(bool $isShortHaul = false): float
    {
        return (float) ($isShortHaul
            ? ($this->short_haul_web_admin_fee ?? 30)
            : ($this->web_admin_fee ?? 175));
    }

    /**
     * Get the transaction fee based on trip haul duration (< 5h is short haul).
     */
    public function getTransactionFee(bool $isShortHaul = false): float
    {
        return (float) ($isShortHaul
            ? ($this->short_haul_transaction_fee ?? 70)
            : ($this->transaction_fee ?? 345));
    }

    public function isSlaVoucherEnabled(): bool
    {
        return (bool) ($this->sla_voucher_enabled ?? true);
    }

    public function getSlaVoucherHours(): int
    {
        return (int) ($this->sla_voucher_hours ?? 2);
    }

    public function getSlaVoucherAmount(): float
    {
        return (float) ($this->sla_voucher_amount ?? 500);
    }

    /**
     * There is always exactly one settings row. Fetch it (or create with
     * defaults), caching the result in Redis for 6 hours.
     *
     * Cache is invalidated any time the row is updated via bust().
     */
    public static function current(): self
    {
        $attributes = Cache::remember('payment_settings:current', now()->addHours(6), function () {
            $model = static::query()->firstOrCreate(['id' => 1], [
                'web_admin_fee'                      => 175,
                'short_haul_web_admin_fee'           => 30,
                'fee_per_accommodation'              => 5000,
                'transaction_fee'                    => 345,
                'short_haul_transaction_fee'         => 70,
                'revalidation_fee'                   => 250,
                'proof_retention_days'               => 30,
                'ferry_before_departure_surcharge_pct'  => 25,
                'ferry_after_departure_surcharge_pct'   => 40,
                'airline_before_departure_surcharge_pct' => 40,
                'rebook_ferry_before_departure_surcharge_pct' => 15,
                'rebook_ferry_after_departure_surcharge_pct'  => 35,
                'rebook_airline_before_departure_surcharge_pct' => 15,
                'sla_voucher_enabled'                => true,
                'sla_voucher_hours'                  => 2,
                'sla_voucher_amount'                 => 500,
            ]);

            return $model->getAttributes();
        });

        $instance = new static;
        $instance->setRawAttributes($attributes, true);
        $instance->exists = true;

        return $instance;
    }

    protected static function booted(): void
    {
        static::saved(function () {
            static::bust();
        });

        static::deleted(function () {
            static::bust();
        });
    }

    /**
     * Bust the PaymentSetting cache.
     * Call this after saving/updating the settings row.
     */
    public static function bust(): void
    {
        try {
            Cache::forget('payment_settings:current');
            Cache::forget('api:payment_settings');
        } catch (\Throwable) {
            // Ignore cache driver errors
        }
    }
}

