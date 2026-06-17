<?php

namespace App\Models;

use App\Enums\WorkerType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Worker extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'worker_type',
        'name',
        'mobile_number',
        'address',
        'profile_image_path',
        'joined_on',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'worker_type' => WorkerType::class,
            'joined_on' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function salaryRates(): HasMany
    {
        return $this->hasMany(WorkerSalaryRate::class)->orderByDesc('effective_from');
    }

    public function profileImageUrl(): ?string
    {
        return $this->profile_image_path
            ? Storage::disk('public')->url($this->profile_image_path)
            : null;
    }
}
