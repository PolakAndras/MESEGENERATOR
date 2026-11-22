{{-- A szereplő közös mezői (S-21, S-22). A nem és a hajszín csak gyerekfiguránál látszik; a character-form.js
     mutatja/rejti, JS nélkül mindig látszik (a szerver állatnál úgyis NULL-ra állítja).
     Shared character fields (S-21, S-22). Gender and hair colour only show for child figures; character-form.js
     shows/hides them, without JS they always show (the server sets them to NULL for animals anyway). --}}
<x-form-field name="name" :label="__('admin.characters.name')" :hint="__('admin.characters.name_hint')"
              :value="$character->name ?? null" maxlength="50" required />

<x-choice-group name="type" :legend="__('admin.characters.type')" :options="__('admin.characters.types')"
                :value="$character->type ?? 'animal'" data-character-type />

<div class="form-row" data-figure-fields>
    <x-choice-group name="gender" :legend="__('admin.characters.gender')" :options="__('wizard.child.genders')"
                    :value="$character->gender ?? null" />
    <x-choice-group name="hair_color" :legend="__('admin.characters.hair_color')" :options="__('wizard.child.hair_colors')"
                    :value="$character->hair_color ?? null" />
</div>
