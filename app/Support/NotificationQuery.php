<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;

class NotificationQuery
{
    /**
     * @param  Builder|Relation  $query
     * @return Builder|Relation
     */
    public static function whereDataEquals($query, string $key, string $value)
    {
        if (self::isPostgres($query)) {
            $safeKey = self::sanitizeKey($key);

            return $query->whereRaw("(data::jsonb->>'{$safeKey}') = ?", [$value]);
        }

        return $query->where("data->{$key}", $value);
    }

    /**
     * @param  array<int, string>  $values
     */
    /**
     * @param  Builder|Relation  $query
     * @param  array<int, string>  $values
     * @return Builder|Relation
     */
    public static function whereDataIn($query, string $key, array $values)
    {
        if ($values === []) {
            return $query->whereRaw('1 = 0');
        }

        if (self::isPostgres($query)) {
            $safeKey = self::sanitizeKey($key);

            return $query->whereIn(DB::raw("(data::jsonb->>'{$safeKey}')"), $values);
        }

        return $query->whereIn("data->{$key}", $values);
    }

    /**
     * @param  Builder|Relation  $query
     */
    private static function isPostgres($query): bool
    {
        $connectionName = $query->getModel()->getConnectionName();

        return DB::connection($connectionName)->getDriverName() === 'pgsql';
    }

    private static function sanitizeKey(string $key): string
    {
        return preg_replace('/[^a-zA-Z0-9_]/', '', $key) ?: 'key';
    }
}
