<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

class HouseSearchQuery
{
    public static function apply(Builder $query, string $term): void
    {
        $term = trim($term);

        if ($term === '') {
            return;
        }

        $collapsed = strtoupper(preg_replace('/\s+/', '', $term) ?? $term);
        $spaced = strtoupper(preg_replace('/\s+/', ' ', $term) ?? $term);

        if (preg_match('/^([AB])[-]?(\d+)$/i', $collapsed, $matches)) {
            $query->where('house_type', strtoupper($matches[1]))
                ->where('house_number', $matches[2]);

            return;
        }

        if (preg_match('/^([AB])$/i', $spaced)) {
            $query->where('house_type', strtoupper($spaced));

            return;
        }

        $query->where(function (Builder $inner) use ($term, $spaced, $collapsed): void {
            $inner->where('house_number', 'like', '%'.$term.'%')
                ->orWhereRaw("CONCAT(house_type, ' ', house_number) = ?", [$spaced])
                ->orWhereRaw("CONCAT(house_type, house_number) = ?", [$collapsed]);
        });
    }
}
