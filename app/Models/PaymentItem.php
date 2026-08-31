<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PaymentItem extends Model
{
    protected $fillable = ['slug', 'label', 'amount', 'is_active'];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public static function getAmount(string $slug, float $fallback = 0): float
    {
        $items = Cache::remember('payment_items', 300, function () {
            return static::where('is_active', true)->pluck('amount', 'slug')->toArray();
        });

        return (float) ($items[$slug] ?? $fallback);
    }

    public static function clearCache(): void
    {
        Cache::forget('payment_items');
    }

    public static function allActive(): array
    {
        return Cache::remember('payment_items', 300, function () {
            return static::where('is_active', true)->pluck('amount', 'slug')->toArray();
        });
    }
}
