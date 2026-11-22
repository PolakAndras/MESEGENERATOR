{{--
    Vászon (App Flow K-06, Tech Stack 6.1, CG 11.3): 4:3-as <div>, háttér és figurák <img>-ként, a tárolt
    százalékos koordinátákon. A szerver mindig kirendereli a figurákat (JS nélkül is látszik a kép); szerkesztő
    módban a CG 11.3 szerinti JSON is az oldalba kerül, ezt olvassa az editor.js.
    canvas: CanvasState szerkezet. alt: háttérnél a név, figuránál „szereplő – cselekvés” (CG 8.1); decorative="true"
    esetén minden alt üres (pl. a címoldal díszképe ismétlés lenne).

    Canvas (App Flow K-06, Tech Stack 6.1, CG 11.3): a 4:3 <div> with background and figures as <img> at the stored
    percentage coordinates. The server always renders the figures (the picture shows without JS); in edit mode the
    CG 11.3 JSON is embedded too, read by editor.js. canvas: CanvasState shape. alt: the name for backgrounds,
    "character – action" for figures (CG 8.1); with decorative="true" every alt is empty.
--}}
@props(['canvas', 'mode' => 'read', 'label' => null, 'decorative' => false])

<div {{ $attributes->merge(['class' => 'story-canvas'.($mode === 'edit' ? ' story-canvas--edit' : '')]) }}
     @if ($mode === 'edit') data-story-canvas @endif
     @if ($label) role="group" aria-label="{{ $label }}" @endif>
    @if ($mode === 'edit')
        <script type="application/json" data-story-canvas-state>@json($canvas)</script>
    @endif

    @if ($canvas['background'])
        <img class="story-canvas__bg" src="{{ $canvas['background']['url'] }}" alt="{{ $decorative ? '' : $canvas['background']['name'] }}"
             width="1600" height="1200" loading="eager" @if ($mode === 'edit') data-canvas-bg @endif>
    @elseif ($mode === 'edit')
        <img class="story-canvas__bg" alt="" width="1600" height="1200" data-canvas-bg hidden>
    @endif

    @foreach ($canvas['placements'] as $p)
        <img class="story-canvas__figure {{ ($p['flipped'] ?? false) ? 'story-canvas__figure--flipped' : '' }}" src="{{ $p['url'] }}"
             alt="{{ $decorative ? '' : $p['character_name'].' – '.$p['action_name'] }}"
             width="800" height="800" loading="eager"
             style="left: {{ $p['pos_x'] }}%; top: {{ $p['pos_y'] }}%; height: {{ $p['scale'] }}%; z-index: {{ $p['z_index'] }};"
             @if ($mode === 'edit') data-canvas-figure="{{ $p['character_id'] }}" @endif>
    @endforeach
</div>
