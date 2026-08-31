<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Voucher extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'discount_type',
        'discount_value',
        'max_discount',
        'min_booking_amount',
        'start_at',
        'end_at',
        'is_active',
        'total_usage_limit',
        'one_use_per_customer',
        'eligible_scope',
        'eligible_origin',
        'eligible_destination',
        'eligible_operator_id',
        'eligible_schedule_id',
        'is_hidden',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'min_booking_amount' => 'decimal:2',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'is_active' => 'boolean',
        'one_use_per_customer' => 'boolean',
        'is_hidden' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (Voucher $voucher) {
            $voucher->code = strtoupper(trim($voucher->code));
        });
    }

    // Scopes
    public function scopeActive($query)
    {
        $query->where('is_active', true)
              ->where(function ($q) {
                  $q->whereNull('start_at')->orWhere('start_at', '<=', now());
              })
              ->where(function ($q) {
                  $q->whereNull('end_at')->orWhere('end_at', '>=', now());
              });
    }

    // Relationships
    public function redemptions(): HasMany
    {
        return $this->hasMany(VoucherRedemption::class);
    }

    public function eligibleOperator(): BelongsTo
    {
        return $this->belongsTo(Operator::class, 'eligible_operator_id');
    }

    public function eligibleSchedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class, 'eligible_schedule_id');
    }

    public function claimedByUsers()
    {
        return $this->belongsToMany(User::class, 'user_hidden_vouchers')->withTimestamps();
    }

    // Helpers
    public function getTotalUsedAttribute(): int
    {
        return $this->redemptions()->count();
    }

    public function getRemainingUsesAttribute(): ?int
    {
        if ($this->total_usage_limit === null) {
            return null;
        }
        
        return max(0, $this->total_usage_limit - $this->total_used);
    }

    public function getTotalDiscountGrantedAttribute(): float
    {
        return $this->redemptions()->sum('discount_amount') ?? 0;
    }
}
