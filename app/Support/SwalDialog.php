<?php

namespace App\Support;

class SwalDialog
{
    /**
     * @param  array{
     *     title?: string,
     *     message: string,
     *     icon?: string,
     *     confirm_text?: string,
     *     code?: string|null,
     *     actions?: list<array{label: string, url?: string, type?: string}>
     * }  $options
     */
    public static function alert(array $options): void
    {
        session()->flash('swal_dialog', [
            'title' => $options['title'] ?? '',
            'message' => $options['message'],
            'icon' => $options['icon'] ?? 'warning',
            'confirm_text' => $options['confirm_text'] ?? __('messages.swal_ok'),
            'code' => $options['code'] ?? null,
            'actions' => $options['actions'] ?? [],
        ]);
    }
}
