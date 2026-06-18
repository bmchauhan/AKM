@props([
    'columns' => [],
    'rows' => [],
    'compact' => false,
])

@php
    $valueToneClass = static function (?string $tone): string {
        return match ($tone) {
            'danger', 'expense' => 'font-semibold text-[#AB1E23]',
            'income' => 'font-semibold text-[#080D21]',
            'primary' => 'font-semibold text-[#080D21]',
            'accent' => 'font-semibold text-[#080D21]',
            default => 'text-[#080D21]',
        };
    };
@endphp

<div {{ $attributes->merge(['class' => 'overflow-hidden rounded-xl border border-[#E6EBF4] bg-white shadow-sm h-full']) }}>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="border-b border-[#E6EBF4] bg-[#E6EBF4]/50 text-xs font-semibold uppercase tracking-wide text-[#080D21]">
                    @foreach ($columns as $column)
                        <th @class([
                            $compact ? 'px-3 py-2' : 'px-4 py-3',
                            'text-left' => ($column['align'] ?? 'left') === 'left',
                            'text-center' => ($column['align'] ?? 'left') === 'center',
                            'text-right' => ($column['align'] ?? 'left') === 'right',
                            'w-24' => ($column['key'] ?? '') === 'action' && $compact,
                            'w-40' => ($column['key'] ?? '') === 'action' && ! $compact,
                        ])>
                            {{ $column['label'] }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr class="border-b border-[#E6EBF4] last:border-b-0 hover:bg-[#ECEAE1]/25">
                        @foreach ($columns as $column)
                            @php
                                $key = $column['key'] ?? '';
                            @endphp
                            <td @class([
                                'align-middle',
                                $compact ? 'px-3 py-2.5' : 'px-4 py-3',
                                'text-left' => ($column['align'] ?? 'left') === 'left',
                                'text-center' => ($column['align'] ?? 'left') === 'center',
                                'text-right' => ($column['align'] ?? 'left') === 'right',
                            ])>
                                @if ($key === 'summary')
                                    <div class="min-w-0">
                                        <p class="truncate font-medium text-[#080D21]">{{ $row['label'] ?? '—' }}</p>
                                        @if (filled($row['value'] ?? null))
                                            <div class="mt-1">
                                                @if (! empty($row['is_granted']))
                                                    <span class="inline-flex rounded-full bg-[#E6C280]/30 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-[#080D21]">
                                                        {{ $row['value'] }}
                                                    </span>
                                                @else
                                                    <span class="text-sm {{ $valueToneClass($row['tone'] ?? null) }}">
                                                        {{ $row['value'] }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                        @if (filled($row['hint'] ?? null) && ($row['hint'] ?? '—') !== '—')
                                            <p class="mt-1 line-clamp-2 text-xs leading-relaxed text-[#0F141E]/60">
                                                {{ $row['hint'] }}
                                            </p>
                                        @endif
                                    </div>
                                @elseif ($key === 'action' && ! empty($row['link']))
                                    <a
                                        href="{{ $row['link'] }}"
                                        class="inline-flex items-center gap-1 text-sm font-semibold text-[#AB1E23] hover:text-[#080D21]"
                                    >
                                        {{ $row['action_label'] ?? __('messages.dashboard_open_link') }}
                                        <span aria-hidden="true">→</span>
                                    </a>
                                @elseif ($key === 'action')
                                    <span class="text-xs text-[#0F141E]/35">—</span>
                                @elseif ($key === 'value')
                                    @if (! empty($row['is_granted']))
                                        <span class="inline-flex rounded-full bg-[#E6C280]/30 px-2.5 py-1 text-xs font-semibold uppercase tracking-wide text-[#080D21]">
                                            {{ $row['value'] ?? '—' }}
                                        </span>
                                    @else
                                        <span class="{{ $valueToneClass($row['tone'] ?? null) }}">
                                            {{ $row['value'] ?? '—' }}
                                        </span>
                                    @endif
                                @elseif ($key === 'label')
                                    <span class="font-medium text-[#080D21]">{{ $row['label'] ?? '—' }}</span>
                                @elseif ($key === 'hint')
                                    <span class="text-xs leading-relaxed text-[#0F141E]/65">{{ $row['hint'] ?? '—' }}</span>
                                @else
                                    {{ $row[$key] ?? '—' }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($columns) }}" class="px-4 py-5 text-center text-sm text-[#0F141E]/50">
                            {{ __('messages.dashboard_table_empty') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
