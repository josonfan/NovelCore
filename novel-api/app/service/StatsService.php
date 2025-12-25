<?php
declare(strict_types=1);

namespace app\service;

use app\model\Stats;
use app\model\User;

class StatsService
{
    protected static function today(): string
    {
        return date('Y-m-d');
    }

    protected static function ensure(string $date): array
    {
        $row = Stats::where('stat_date', $date)->find();
        if ($row) {
            return $row->toArray();
        }
        $prevDate = date('Y-m-d', strtotime($date . ' -1 day'));
        $prev = Stats::where('stat_date', $prevDate)->find();
        $prevUserCount = $prev ? (int)($prev->user_count ?? 0) : (int)User::count('id');
        $m = new Stats();
        $m->writeById(0, [
            'stat_date'   => $date,
            'user_count'  => $prevUserCount,
            'read_count'  => 0,
            'order_count' => 0,
            'order_amount'=> 0.00,
        ]);
        return $m->toArray();
    }

    public static function onUserRegistered(): void
    {
        $date = self::today();
        $row = self::ensure($date);
        (new Stats())->writeById((int)$row['id'], [
            'user_count' => (int)$row['user_count'] + 1,
        ]);
    }

    public static function incReadCount(int $delta = 1): void
    {
        $date = self::today();
        $row = self::ensure($date);
        (new Stats())->writeById((int)$row['id'], [
            'read_count' => (int)$row['read_count'] + max(1, $delta),
        ]);
    }

    public static function incOrderCount(int $delta = 1): void
    {
        $date = self::today();
        $row = self::ensure($date);
        (new Stats())->writeById((int)$row['id'], [
            'order_count' => (int)$row['order_count'] + max(1, $delta),
        ]);
    }

    public static function addOrderAmount(float $amount): void
    {
        $date = self::today();
        $row = self::ensure($date);
        (new Stats())->writeById((int)$row['id'], [
            'order_amount' => (float)$row['order_amount'] + max(0.0, $amount),
        ]);
    }
}
