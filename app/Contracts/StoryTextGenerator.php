<?php

namespace App\Contracts;

/**
 * A mese szövegét író szolgáltatás közös felülete (Tech Stack 5.1, PRD NFR-15). A kontrollerek csak ezt ismerik,
 * így a szolgáltató (Groq, Mistral, demo) a STORY_GENERATOR beállítással cserélhető, a tesztek pedig szimulálhatják.
 *
 * Common interface of the service that writes the story text (Tech Stack 5.1, PRD NFR-15). Controllers only know this,
 * so the provider (Groq, Mistral, demo) is swapped via STORY_GENERATOR, and tests can simulate it.
 */
interface StoryTextGenerator
{
    /**
     * @param  array  $story  ['child' => [name, age, gender], 'context' => string,
     *                        'pages' => [['page' => int, 'background' => string, 'child_action' => ?string,
     *                        'animals' => [['name' => string, 'action' => string]]]]]
     * @return string a modell nyers válasza, a PRD US-07 AC4 szerinti JSON / the model's raw answer, JSON per US-07 AC4
     *
     * @throws \App\Exceptions\StoryGenerationException ha a szolgáltató nem érhető el / if the provider is unavailable
     */
    public function generate(array $story): string;
}
