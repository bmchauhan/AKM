<x-layouts.frontend :title="__('messages.privacy').' | '.__('messages.brand_name')">
    <x-frontend.page-eyebrow-banner
        :eyebrow="__('messages.privacy')"
        :title="__('messages.privacy_page_title')"
        :subtitle="__('messages.privacy_page_subtitle')"
    />

    <x-frontend.legal-document
        updated-key="messages.privacy_last_updated"
        sections-key="messages.privacy_sections"
    />
</x-layouts.frontend>
