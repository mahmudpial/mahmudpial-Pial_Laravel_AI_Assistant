<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class QuotaTracker
{
    private const DAILY_LIMIT = 250;
    private const LOW_QUOTA_THRESHOLD = 20;

    /**
     * Get today's cache key for quota tracking
     */
    private static function getDayKey(): string
    {
        return 'gemini-quota-' . now()->format('Y-m-d');
    }

    /**
     * Record an API request
     */
    public static function recordRequest(): void
    {
        $dayKey = self::getDayKey();
        $current = Cache::get($dayKey, 0);
        // Store for entire day (86400 seconds)
        Cache::put($dayKey, $current + 1, 86400);
    }

    /**
     * Get today's request count
     */
    public static function getTodayCount(): int
    {
        return Cache::get(self::getDayKey(), 0);
    }

    /**
     * Get remaining requests for today
     */
    public static function getRemaining(): int
    {
        $used = self::getTodayCount();
        $remaining = self::DAILY_LIMIT - $used;
        return max(0, $remaining);
    }

    /**
     * Get remaining percentage
     */
    public static function getRemainingPercent(): float
    {
        return round((self::getRemaining() / self::DAILY_LIMIT) * 100, 1);
    }

    /**
     * Check if quota is low
     */
    public static function isLow(): bool
    {
        return self::getRemaining() <= self::LOW_QUOTA_THRESHOLD;
    }

    /**
     * Check if quota is exhausted
     */
    public static function isExhausted(): bool
    {
        return self::getRemaining() <= 0;
    }

    /**
     * Get status message
     */
    public static function getStatus(): string
    {
        $remaining = self::getRemaining();
        $percent = self::getRemainingPercent();

        if (self::isExhausted()) {
            return "⛔ Quota exhausted (0/{self::DAILY_LIMIT})";
        }

        if (self::isLow()) {
            return "⚠️  Low quota: {$remaining}/{self::DAILY_LIMIT} ({$percent}%)";
        }

        return "✅ {$remaining}/{self::DAILY_LIMIT} ({$percent}%)";
    }

    /**
     * Get status color class
     */
    public static function getStatusClass(): string
    {
        if (self::isExhausted()) {
            return 'status-exhausted';
        }

        if (self::isLow()) {
            return 'status-low';
        }

        return 'status-ok';
    }

    /**
     * Reset quota (admin only, for testing)
     */
    public static function reset(): void
    {
        Cache::forget(self::getDayKey());
    }
}
