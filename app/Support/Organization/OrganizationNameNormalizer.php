<?php

namespace App\Support\Organization;

use Illuminate\Support\Str;

class OrganizationNameNormalizer
{
    public static function display(?string $value): ?string
    {
        $value = preg_replace('/\s+/u', ' ', trim((string) $value));

        return $value !== '' ? $value : null;
    }

    public static function key(?string $value): ?string
    {
        $display = self::display($value);

        return $display !== null ? Str::lower($display) : null;
    }

    /**
     * @param  iterable<string|null>  $values
     * @return list<string>
     */
    public static function unique(iterable $values): array
    {
        $unique = [];

        foreach ($values as $value) {
            $display = self::display($value);
            $key = self::key($display);

            if ($display === null || $key === null) {
                continue;
            }

            $unique[$key] = $display;
        }

        return array_values($unique);
    }

    /**
     * @return list<string>
     */
    public static function split(?string $value): array
    {
        if ($value === null) {
            return [];
        }

        $parts = preg_split('/[\r\n,;]+/u', $value) ?: [];

        return self::unique($parts);
    }
}
