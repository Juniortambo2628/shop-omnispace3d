<?php

namespace App\Support;

class FuzzySearch
{
    public static function normalize(string $value): string
    {
        return strtolower(trim(preg_replace('/\s+/', ' ', $value)));
    }

    public static function matchesText(string $haystack, string $needle): bool
    {
        $haystack = self::normalize($haystack);
        $needle = self::normalize($needle);

        if ($needle === '') {
            return true;
        }

        if (str_contains($haystack, $needle)) {
            return true;
        }

        return self::isSubsequence($haystack, $needle);
    }

    public static function isSubsequence(string $haystack, string $needle): bool
    {
        if ($needle === '') {
            return true;
        }

        $i = 0;
        $len = strlen($needle);

        for ($j = 0, $hLen = strlen($haystack); $j < $hLen && $i < $len; $j++) {
            if ($haystack[$j] === $needle[$i]) {
                $i++;
            }
        }

        return $i === $len;
    }

    /**
     * @param  array<string, mixed>  $record
     * @param  array<int, string>  $fields
     */
    public static function matchesRecord(array $record, ?string $query, array $fields): bool
    {
        $query = self::normalize($query ?? '');

        if ($query === '') {
            return true;
        }

        $terms = preg_split('/\s+/', $query, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $haystacks = [];

        foreach ($fields as $field) {
            $value = $record[$field] ?? '';

            if (is_string($value) || is_numeric($value)) {
                $haystacks[] = (string) $value;
            }
        }

        $combined = self::normalize(implode(' ', $haystacks));

        foreach ($terms as $term) {
            $termMatched = false;

            foreach ($haystacks as $haystack) {
                if (self::matchesText($haystack, $term)) {
                    $termMatched = true;
                    break;
                }
            }

            if (! $termMatched && ! self::matchesText($combined, $term)) {
                return false;
            }
        }

        return true;
    }

    public static function fieldMatchScore(?string $haystack, string $term, int $weight): int
    {
        $haystack = self::normalize((string) $haystack);
        $term = self::normalize($term);

        if ($term === '' || $haystack === '') {
            return 0;
        }

        if (str_starts_with($haystack, $term)) {
            return ($weight * 1000) + max(0, 50 - strlen($term));
        }

        if (str_contains($haystack, $term)) {
            return ($weight * 500) + max(0, 50 - strlen($term));
        }

        if (self::isSubsequence($haystack, $term)) {
            return $weight * 100;
        }

        return 0;
    }

    /**
     * @param  array<string, mixed>  $record
     */
    public static function productMatchScore(array $record, ?string $query): int
    {
        $query = self::normalize($query ?? '');

        if ($query === '') {
            return PHP_INT_MAX;
        }

        $terms = preg_split('/\s+/', $query, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $code = (string) ($record['code'] ?? '');
        $name = (string) ($record['name'] ?? '');
        $total = 0;

        foreach ($terms as $term) {
            $codeScore = self::fieldMatchScore($code, $term, 10);
            $nameScore = self::fieldMatchScore($name, $term, 3);
            $termScore = max($codeScore, $nameScore);

            if ($termScore === 0) {
                return 0;
            }

            $total += $termScore;
        }

        return $total;
    }

    /**
     * Product search: code is primary, name is supplementary.
     *
     * @param  array<string, mixed>  $record
     */
    public static function matchesProductRecord(array $record, ?string $query): bool
    {
        return self::productMatchScore($record, $query) > 0;
    }
}
