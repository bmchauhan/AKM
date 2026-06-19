<x-layouts.admin :pageTitle="__('messages.settings_email')">
    <div class="space-y-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-[#AB1E23]">
                {{ __('messages.settings') }}
            </p>
            <h2 class="mt-1 text-xl font-bold text-[#080D21]">{{ __('messages.settings_email') }}</h2>
            <p class="mt-1 text-sm text-[#0F141E]/70">{{ __('messages.email_settings_subtitle') }}</p>
        </div>

        @can('settings_email.update')
            <form
                method="POST"
                action="{{ route('admin.settings.email.update') }}"
                class="rounded-xl border border-[#E6EBF4] bg-white p-5 shadow-sm sm:p-6"
            >
                @csrf
                @method('PUT')

                <h3 class="text-sm font-bold uppercase tracking-wide text-[#080D21]">{{ __('messages.email_settings_delivery_title') }}</h3>
                <p class="mt-1 text-sm text-[#0F141E]/70">{{ __('messages.email_settings_delivery_hint') }}</p>

                <div class="mt-5 flex items-start gap-4 rounded-lg border border-[#E6EBF4] bg-[#E6EBF4]/40 px-4 py-4">
                    <input type="hidden" name="emails_enabled" value="0">
                    <input
                        type="checkbox"
                        name="emails_enabled"
                        id="emails_enabled"
                        value="1"
                        @checked(old('emails_enabled', $setting->emails_enabled))
                        class="mt-1 h-4 w-4 rounded border-[#E6EBF4] text-[#AB1E23] focus:ring-[#AB1E23]/20"
                    >
                    <label for="emails_enabled" class="cursor-pointer">
                        <span class="block text-sm font-semibold text-[#080D21]">{{ __('messages.email_settings_enable_label') }}</span>
                        <span class="mt-1 block text-sm text-[#0F141E]/70">{{ __('messages.email_settings_enable_hint') }}</span>
                    </label>
                </div>

                @error('emails_enabled')
                    <p class="mt-2 text-sm text-[#AB1E23]">{{ $message }}</p>
                @enderror

                @if ($setting->updatedBy)
                    <p class="mt-4 text-xs text-[#0F141E]/50">
                        {{ __('messages.email_settings_last_updated', [
                            'name' => $setting->updatedBy->fullName(),
                            'date' => $setting->updated_at?->format('d M Y, h:i A'),
                        ]) }}
                    </p>
                @endif

                <div class="mt-6">
                    <x-common.button type="submit">{{ __('messages.email_settings_save') }}</x-common.button>
                </div>
            </form>
        @else
            <div class="rounded-xl border border-[#E6EBF4] bg-white p-5 shadow-sm sm:p-6">
                <p class="text-sm font-semibold text-[#080D21]">{{ __('messages.email_settings_enable_label') }}</p>
                <p class="mt-2 text-sm text-[#0F141E]/80">
                    {{ $setting->emails_enabled ? __('messages.email_settings_status_enabled') : __('messages.email_settings_status_disabled') }}
                </p>
            </div>
        @endcan

        <div class="overflow-hidden rounded-xl border border-[#E6EBF4] bg-white shadow-sm">
            <div class="border-b border-[#E6EBF4] bg-[#E6EBF4]/50 px-4 py-4 sm:px-6">
                <h3 class="text-sm font-bold uppercase tracking-wide text-[#080D21]">{{ __('messages.email_logs_title') }}</h3>
                <p class="mt-1 text-sm text-[#0F141E]/70">{{ __('messages.email_logs_subtitle') }}</p>
            </div>

            @if ($logs->total() === 0)
                <div class="px-4 py-10 text-center sm:px-6">
                    <p class="text-sm text-[#0F141E]/70">{{ __('messages.email_logs_empty') }}</p>
                </div>
            @else
                <div class="hidden border-b border-[#E6EBF4] bg-[#ECEAE1]/60 px-4 py-3 text-xs font-semibold uppercase tracking-wide text-[#080D21] lg:grid lg:grid-cols-12 lg:gap-3">
                    <div class="lg:col-span-2">{{ __('messages.email_logs_date') }}</div>
                    <div class="lg:col-span-2">{{ __('messages.email_logs_type') }}</div>
                    <div class="lg:col-span-2">{{ __('messages.email_logs_recipient') }}</div>
                    <div class="lg:col-span-2">{{ __('messages.email_logs_subject') }}</div>
                    <div class="lg:col-span-2">{{ __('messages.email_logs_account') }}</div>
                    <div class="lg:col-span-1">{{ __('messages.email_logs_status') }}</div>
                    <div class="lg:col-span-1">{{ __('messages.email_logs_triggered_by') }}</div>
                </div>

                <div class="divide-y divide-[#E6EBF4]">
                    @foreach ($logs as $log)
                        <div class="px-4 py-4 sm:px-6 lg:grid lg:grid-cols-12 lg:items-start lg:gap-3">
                            <div class="lg:col-span-2">
                                <p class="text-xs font-semibold uppercase tracking-wide text-[#080D21]/50 lg:hidden">{{ __('messages.email_logs_date') }}</p>
                                <p class="text-sm text-[#0F141E]">{{ $log->created_at?->format('d M Y') }}</p>
                                <p class="text-xs text-[#0F141E]/50">{{ $log->created_at?->format('h:i A') }}</p>
                            </div>

                            <div class="mt-3 lg:col-span-2 lg:mt-0">
                                <p class="text-xs font-semibold uppercase tracking-wide text-[#080D21]/50 lg:hidden">{{ __('messages.email_logs_type') }}</p>
                                <p class="text-sm text-[#0F141E]">{{ $log->mail_type->label() }}</p>
                            </div>

                            <div class="mt-3 lg:col-span-2 lg:mt-0">
                                <p class="text-xs font-semibold uppercase tracking-wide text-[#080D21]/50 lg:hidden">{{ __('messages.email_logs_recipient') }}</p>
                                <p class="text-sm text-[#0F141E]">{{ $log->recipient_email }}</p>
                                @if ($log->recipientUser)
                                    <p class="text-xs text-[#0F141E]/50">{{ $log->recipientUser->fullName() }}</p>
                                @endif
                            </div>

                            <div class="mt-3 lg:col-span-2 lg:mt-0">
                                <p class="text-xs font-semibold uppercase tracking-wide text-[#080D21]/50 lg:hidden">{{ __('messages.email_logs_subject') }}</p>
                                <p class="text-sm text-[#0F141E]">{{ $log->subject }}</p>
                            </div>

                            <div class="mt-3 lg:col-span-2 lg:mt-0">
                                <p class="text-xs font-semibold uppercase tracking-wide text-[#080D21]/50 lg:hidden">{{ __('messages.email_logs_account') }}</p>
                                <p class="text-sm text-[#0F141E]">{{ $log->subjectUser?->fullName() ?? '—' }}</p>
                            </div>

                            <div class="mt-3 lg:col-span-1 lg:mt-0">
                                <p class="text-xs font-semibold uppercase tracking-wide text-[#080D21]/50 lg:hidden">{{ __('messages.email_logs_status') }}</p>
                                @php
                                    $statusClasses = match ($log->status->value) {
                                        'sent' => 'bg-[#E6EBF4] text-[#080D21]',
                                        'skipped' => 'bg-[#E6C280]/30 text-[#080D21]',
                                        default => 'bg-[#E5989B]/20 text-[#AB1E23]',
                                    };
                                @endphp
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClasses }}">
                                    {{ $log->status->label() }}
                                </span>
                                @if ($log->skip_reason)
                                    <p class="mt-1 text-xs text-[#0F141E]/50">{{ $log->skip_reason->label() }}</p>
                                @endif
                                @if ($log->error_message)
                                    <p class="mt-1 text-xs text-[#AB1E23]">{{ Str::limit($log->error_message, 80) }}</p>
                                @endif
                            </div>

                            <div class="mt-3 lg:col-span-1 lg:mt-0">
                                <p class="text-xs font-semibold uppercase tracking-wide text-[#080D21]/50 lg:hidden">{{ __('messages.email_logs_triggered_by') }}</p>
                                <p class="text-sm text-[#0F141E]">{{ $log->triggeredBy?->fullName() ?? '—' }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <x-common.pagination :paginator="$logs" class="border-t border-[#E6EBF4]" />
            @endif
        </div>
    </div>
</x-layouts.admin>
