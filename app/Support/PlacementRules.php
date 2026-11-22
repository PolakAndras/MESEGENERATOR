<?php

namespace App\Support;

/**
 * DOM-független elhelyezési szabályok (Backend Schema 7.4, CG 8.4); a JS párja a public/js/lib/placement.js.
 * Ugyanez a kód számol validáláskor, megjelenítéskor és mentéskor, így a három nem térhet el egymástól.
 *
 * DOM-independent placement rules (Backend Schema 7.4, CG 8.4); the JS twin is public/js/lib/placement.js.
 * The same code runs for validation, rendering and saving, so the three can never disagree.
 */
class PlacementRules
{
    public static function scale(): float
    {
        return (float) config('story.placement.default_scale');
    }

    // A figura nem lóghat ki: pos_x ∈ [h; 100 − h], pos_y ∈ [s; 100], ahol h = s × 0,375 (1:1 kép a 4:3 vásznon).
    // The figure must not overflow: pos_x ∈ [h; 100 − h], pos_y ∈ [s; 100], where h = s × 0.375 (1:1 image on 4:3 canvas).
    public static function bounds(?float $scale = null): array
    {
        $s = $scale ?? self::scale();
        $h = $s * (float) config('story.placement.half_width_factor');

        return ['min_x' => $h, 'max_x' => 100 - $h, 'min_y' => $s, 'max_y' => 100.0];
    }

    public static function isWithin(float $x, float $y, ?float $scale = null): bool
    {
        $b = self::bounds($scale);

        return $x >= $b['min_x'] && $x <= $b['max_x'] && $y >= $b['min_y'] && $y <= $b['max_y'];
    }

    /**
     * Rétegsorrend: pos_y szerint növekvően, egyenlőségnél a hozzáadás sorrendjében; z_index 1…N folytonos (US-06 AC5).
     * Layer order: ascending pos_y, ties keep insertion order; z_index is a continuous 1…N (US-06 AC5).
     *
     * @param  array  $placements  hozzáadási sorrendben / in insertion order
     * @return array ugyanazok az elemek z_index kulccsal, eredeti sorrendben / same items with z_index, original order
     */
    public static function withZIndex(array $placements): array
    {
        $order = array_keys($placements);
        // A usort nem stabil PHP 8.0 alatt, ezért az eredeti indexet is összehasonlítjuk.
        // usort is not stable before PHP 8.0, so the original index is compared too.
        usort($order, function ($a, $b) use ($placements) {
            return [(float) $placements[$a]['pos_y'], $a] <=> [(float) $placements[$b]['pos_y'], $b];
        });

        foreach ($order as $i => $key) {
            $placements[$key]['z_index'] = $i + 1;
        }

        return $placements;
    }
}
