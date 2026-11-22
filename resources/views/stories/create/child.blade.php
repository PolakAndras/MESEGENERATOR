{{--
    S-07 – 1. lépés: a gyerek adatai (App Flow 4.2, PRD US-01). A nem + hajszín alapján a figura előnézete;
    a child-preview.js frissíti választáskor, JS nélkül a mentett választás képe látszik.
    S-07 – Step 1: the child's data (App Flow 4.2, PRD US-01). A figure preview from gender + hair colour;
    child-preview.js updates it on change, without JS the saved choice's image shows.
--}}
@push('scripts')
    <script type="module" src="{{ asset('js/child-preview.js') }}"></script>
@endpush

@php
    $gender = old('child_gender', $child['gender'] ?? null);
    $hair = old('child_hair_color', $child['hair_color'] ?? null);
    $previewUrl = ($gender && $hair) ? ($previews[$gender.'-'.$hair] ?? null) : null;
@endphp

<x-layouts.app :title="__('wizard.child.title')">
    <div class="app-layout__content app-layout__content--form">
        <x-wizard-steps :current="1" />

        <h1>{{ __('wizard.child.title') }}</h1>
        <p class="lead">{{ __('wizard.child.lead') }}</p>

        <x-error-summary />

        <form method="POST" action="{{ route('stories.create.child.store') }}" class="card form" novalidate>
            @csrf
            <x-form-field name="child_name" :label="__('wizard.child.name')"
                          :hint="__('wizard.child.name_hint', ['max' => config('story.child_name_max')])"
                          :value="$child['name'] ?? null" maxlength="{{ config('story.child_name_max') }}" autocomplete="off" required />

            <x-form-field name="child_age" type="number" :label="__('wizard.child.age')"
                          :hint="__('wizard.child.age_hint', ['min' => config('story.child_age_min'), 'max' => config('story.child_age_max')])"
                          :value="$child['age'] ?? null" min="{{ config('story.child_age_min') }}" max="{{ config('story.child_age_max') }}"
                          inputmode="numeric" required />

            <div class="child-figure" data-child-preview data-previews='@json($previews)'>
                <div class="child-figure__fields">
                    <x-choice-group name="child_gender" :legend="__('wizard.child.gender')"
                                    :options="__('wizard.child.genders')" :value="$child['gender'] ?? null" />
                    <x-choice-group name="child_hair_color" :legend="__('wizard.child.hair_color')"
                                    :options="__('wizard.child.hair_colors')" :value="$child['hair_color'] ?? null" />
                </div>
                <figure class="child-figure__preview">
                    <div class="child-figure__frame">
                        <img class="child-figure__image" src="{{ $previewUrl }}" alt="" width="800" height="800"
                             data-child-preview-image @if (! $previewUrl) hidden @endif>
                        <p class="child-figure__empty" data-child-preview-empty @if ($previewUrl) hidden @endif>{{ __('wizard.child.preview_empty') }}</p>
                    </div>
                    <figcaption class="child-figure__caption">{{ __('wizard.child.preview') }}</figcaption>
                </figure>
            </div>

            <div class="actions actions--end">
                <x-button variant="primary">{{ __('wizard.next') }} <x-icon name="arrow-right" /></x-button>
            </div>
        </form>
    </div>
</x-layouts.app>
