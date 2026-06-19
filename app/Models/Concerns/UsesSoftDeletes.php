<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\SoftDeletes;
use LogicException;

trait UsesSoftDeletes
{
    use SoftDeletes;

    public function forceDelete(): ?bool
    {
        throw new LogicException(__('messages.hard_delete_forbidden'));
    }

    protected static function bootUsesSoftDeletes(): void
    {
        static::deleting(function (self $model): void {
            if ($model->isForceDeleting()) {
                throw new LogicException(__('messages.hard_delete_forbidden'));
            }
        });
    }
}
