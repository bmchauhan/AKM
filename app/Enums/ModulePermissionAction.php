<?php

namespace App\Enums;

enum ModulePermissionAction: string
{
    case Create = 'create';
    case Read = 'read';
    case Update = 'update';
    case Delete = 'delete';

    public function column(): string
    {
        return 'can_'.$this->value;
    }

    /**
     * @return list<string>
     */
    public static function columns(): array
    {
        return array_map(fn (self $action) => $action->column(), self::cases());
    }
}
