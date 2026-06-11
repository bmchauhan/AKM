<?php

namespace App\Support;

class Toast
{
    public static function success(string $message): void
    {
        self::flash('success', $message);
    }

    public static function error(string $message): void
    {
        self::flash('error', $message);
    }

    public static function warning(string $message): void
    {
        self::flash('warning', $message);
    }

    public static function info(string $message): void
    {
        self::flash('info', $message);
    }

    public static function flash(string $type, string $message): void
    {
        session()->flash('toasts', [[
            'type' => $type,
            'message' => $message,
        ]]);
    }
}
