<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class QuotaTracker
{
    // gemini-2.0-flash-lite free tier limits
    private const DAILY_LIMIT = 1500;
    private const RPM_LIMIT = 30;
    private const LOW_THRESHOLD = 100;  // warn when < 100 daily remaining

    // ── Cache keys ────────────────────────────────────────────

    private static function dayKey(): string
    {
        return 'gemini_quota_day_' . now()->utc()->format('Y-m-d');
    }

    private static function minuteKey(): string
    {
        // Changes every minute; TTL of 60s ensures auto-expiry
        return 'gemini_quota_rpm_' . now()->utc()->format('Y-m-d_H-i');
    }

    // ── Write ──────────────────────────────────────────────────

    /**
     * Record one API request. Call this right before sending to Gemini.
     */
    public static function recordRequest(): void
    {
        // Daily — expires precisely at UTC midnight
        $ttlDay = now()->utc()->secondsUntilEndOfDay() + 1;
        Cache::put(self::dayKey(), self::getTodayCount() + 1, $ttlDay);

        // Per-minute — auto-expires after 60 s
        Cache::put(self::minuteKey(), self::getCurrentMinuteCount() + 1, 60);
    }

    // ── Daily quota ────────────────────────────────────────────

    public static function getTodayCount(): int
    {
        return (int) Cache::get(self::dayKey(), 0);
    }

    public static function getRemaining(): int
    {
        return max(0, self::DAILY_LIMIT - self::getTodayCount());
    }

    public static function getRemainingPercent(): float
    {
        return round((self::getRemaining() / self::DAILY_LIMIT) * 100, 1);
    }

    public static function isExhausted(): bool
    {
        return self::getRemaining() <= 0;
    }

    public static function isLow(): bool
    {
        return !self::isExhausted() && self::getRemaining() <= self::LOW_THRESHOLD;
    }

    // ── Per-minute (RPM) quota ─────────────────────────────────

    public static function getCurrentMinuteCount(): int
    {
        return (int) Cache::get(self::minuteKey(), 0);
    }

    public static function getRpmRemaining(): int
    {
        return max(0, self::RPM_LIMIT - self::getCurrentMinuteCount());
    }

    /**
     * Returns true when the per-minute cap is reached.
     * Check this BEFORE calling recordRequest().
     */
    public static function isRpmExhausted(): bool
    {
        return self::getCurrentMinuteCount() >= self::RPM_LIMIT;
    }

    // ── Status helpers ─────────────────────────────────────────

    public static function getStatus(): string
    {
        $remaining = self::getRemaining();
        $percent = self::getRemainingPercent();
        $limit = self::DAILY_LIMIT;

        if (self::isExhausted()) {
            return '⛔ Quota exhausted (0/' . $limit . ')';
        }

        if (self::isLow()) {
            return '⚠️  Low quota: ' . $remaining . '/' . $limit . ' (' . $percent . '%)';
        }

        return '✅ ' . $remaining . '/' . $limit . ' (' . $percent . '%)';
    }

    public static function getStatusClass(): string
    {
        if (self::isExhausted())
            return 'status-exhausted';
        if (self::isLow())
            return 'status-low';
        return 'status-ok';
    }

    public static function getFillClass(): string
    {
        if (self::isExhausted())
            return 'exhausted';
        if (self::isLow())
            return 'low';
        return '';
    }

    // ── Utility ────────────────────────────────────────────────

    /** Reset all quota counters (for testing / admin use). */
    public static function reset(): void
    {
        Cache::forget(self::dayKey());
        Cache::forget(self::minuteKey());
    }
}