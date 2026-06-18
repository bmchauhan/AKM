<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UsefulDirectoryRole extends Model
{
    protected $fillable = [
        'slug',
        'name_en',
        'name_hi',
        'name_gu',
        'sort_order',
        'supports_committee_link',
        'is_system',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'supports_committee_link' => 'boolean',
            'is_system' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(UsefulDirectoryContact::class, 'directory_role_id');
    }

    public function localizedName(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();

        $name = match ($locale) {
            'hi' => $this->name_hi,
            'gu' => $this->name_gu,
            default => $this->name_en,
        };

        return filled($name) ? $name : $this->name_en;
    }

    /**
     * @return array{id: int, slug: string, label: string, supports_committee_link: bool}
     */
    public function toTabOption(): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'label' => $this->localizedName(),
            'supports_committee_link' => $this->supports_committee_link,
        ];
    }
}
