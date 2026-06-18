<x-layouts.frontend :title="__('messages.terms').' | '.__('messages.brand_name')">
    <x-frontend.page-eyebrow-banner
        :eyebrow="__('messages.terms')"
        :title="__('messages.terms_page_title')"
        :subtitle="__('messages.terms_page_subtitle')"
    />

    <x-frontend.legal-document
        updated-key="messages.terms_last_updated"
        sections-key="messages.terms_sections"
    />
</x-layouts.frontend>
