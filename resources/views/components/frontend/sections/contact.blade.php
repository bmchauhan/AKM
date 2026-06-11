@php
    $usefulContacts = [
        ['title' => __('messages.contact_office_title'), 'detail' => __('messages.contact_office_detail'), 'href' => 'tel:+912612345678', 'icon' => 'phone'],
        ['title' => __('messages.contact_email_title'), 'detail' => __('messages.contact_email_detail'), 'href' => 'mailto:info@aksharyug.com', 'icon' => 'mail'],
        ['title' => __('messages.contact_security_title'), 'detail' => __('messages.contact_security_detail'), 'href' => 'tel:+912612345679', 'icon' => 'shield'],
        ['title' => __('messages.contact_emergency_title'), 'detail' => __('messages.contact_emergency_detail'), 'href' => 'tel:100', 'icon' => 'phone'],
        ['title' => __('messages.contact_hours_title'), 'detail' => __('messages.contact_hours_detail'), 'href' => null, 'icon' => 'clock'],
        ['title' => __('messages.contact_maintenance_title'), 'detail' => __('messages.contact_maintenance_detail'), 'href' => 'tel:+912612345680', 'icon' => 'tool'],
    ];
@endphp

<section
    id="contact"
    data-section="contact"
    class="scroll-mt-24 w-full border-t border-[#E6C280]/30 bg-white py-12 sm:py-16"
>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-[#AB1E23]">
                {{ __('messages.contact_eyebrow') }}
            </p>
            <h2 class="mt-2 text-3xl font-bold text-[#080D21] sm:text-4xl">
                {{ __('messages.contact') }}
            </h2>
            <p class="mt-3 text-base leading-relaxed text-[#0F141E]">
                {{ __('messages.contact_subtitle') }}
            </p>
        </div>

        <div class="mt-10 grid gap-8 lg:grid-cols-2 lg:gap-12">
            <div class="rounded-2xl border border-[#E6EBF4] bg-[#ECEAE1]/40 p-6 shadow-sm sm:p-8">
                <h3 class="text-lg font-bold text-[#080D21]">
                    {{ __('messages.contact_form_title') }}
                </h3>
                <p class="mt-1 text-sm text-[#0F141E]/70">
                    {{ __('messages.contact_form_subtitle') }}
                </p>

                <form action="#" method="POST" class="mt-6 space-y-4">
                    @csrf

                    <x-common.input
                        name="name"
                        :label="__('messages.contact_form_name')"
                        :placeholder="__('messages.contact_form_name_placeholder')"
                        required
                    />

                    <x-common.input
                        name="email"
                        type="email"
                        :label="__('messages.contact_form_email')"
                        :placeholder="__('messages.contact_form_email_placeholder')"
                        required
                    />

                    <x-common.input
                        name="phone"
                        type="tel"
                        :label="__('messages.contact_form_phone')"
                        :placeholder="__('messages.contact_form_phone_placeholder')"
                        required
                    />

                    <div>
                        <label for="message" class="mb-1.5 block text-sm font-semibold text-[#080D21]">
                            {{ __('messages.contact_form_message') }}
                            <span class="text-[#AB1E23]">*</span>
                        </label>
                        <textarea
                            name="message"
                            id="message"
                            rows="4"
                            required
                            placeholder="{{ __('messages.contact_form_message_placeholder') }}"
                            class="w-full resize-y rounded-lg border border-[#E6EBF4] bg-white px-4 py-2.5 text-sm text-[#0F141E] shadow-sm transition placeholder:text-[#0F141E]/40 focus:border-[#AB1E23] focus:outline-none focus:ring-2 focus:ring-[#AB1E23]/20"
                        ></textarea>
                    </div>

                    <x-common.button type="submit" class="w-full sm:w-auto">
                        {{ __('messages.contact_form_submit') }}
                    </x-common.button>
                </form>
            </div>

            <div>
                <h3 class="text-lg font-bold text-[#080D21]">
                    {{ __('messages.contact_useful_title') }}
                </h3>
                <p class="mt-1 text-sm text-[#0F141E]/70">
                    {{ __('messages.contact_useful_subtitle') }}
                </p>

                <div class="mt-6 space-y-3">
                    @foreach ($usefulContacts as $contact)
                        <x-frontend.useful-contact-card
                            :title="$contact['title']"
                            :detail="$contact['detail']"
                            :href="$contact['href']"
                            :icon="$contact['icon']"
                        />
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
