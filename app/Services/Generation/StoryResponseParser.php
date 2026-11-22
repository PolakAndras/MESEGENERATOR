<?php

namespace App\Services\Generation;

use App\Exceptions\StoryGenerationException;
use Illuminate\Support\Str;

/**
 * Az AI nyers válaszának ellenőrzése, mielőtt bármi felhasználná (PRD US-07 AC4, CLAUDE.md).
 * Elfogadja, ha a modell a JSON-t kódblokkba vagy rövid kísérőszöveg közé teszi, de a szerkezetet szigorúan nézi:
 * a cím és minden oldal szövege nem üres, és pontosan az elvárt oldalak szerepelnek.
 *
 * Checks the AI's raw answer before anything uses it (PRD US-07 AC4, CLAUDE.md).
 * It tolerates JSON wrapped in a code fence or short surrounding text, but checks the structure strictly:
 * the title and every page text are non-empty, and exactly the expected pages are present.
 */
class StoryResponseParser
{
    public const TITLE_MAX = 150;  // stories.title VARCHAR(150), Backend Schema 3.5

    /**
     * @return array ['title' => string, 'pages' => [1 => string, …]]
     *
     * @throws StoryGenerationException
     */
    public static function parse(string $raw, int $pageCount): array
    {
        $start = strpos($raw, '{');
        $end = strrpos($raw, '}');
        $data = ($start !== false && $end !== false) ? json_decode(substr($raw, $start, $end - $start + 1), true) : null;

        if (! is_array($data) || ! is_string($data['title'] ?? null) || ! is_array($data['pages'] ?? null)) {
            throw new StoryGenerationException(StoryGenerationException::INVALID_RESPONSE, 'Missing title or pages.');
        }

        $title = trim($data['title']);
        $pages = [];
        foreach ($data['pages'] as $item) {
            $number = is_array($item) ? filter_var($item['page'] ?? null, FILTER_VALIDATE_INT) : false;
            $text = is_array($item) && is_string($item['text'] ?? null) ? trim($item['text']) : '';
            if ($number === false || $text === '' || isset($pages[$number])) {
                throw new StoryGenerationException(StoryGenerationException::INVALID_RESPONSE, 'Invalid page item.');
            }
            $pages[$number] = $text;
        }

        ksort($pages);
        if ($title === '' || array_keys($pages) !== range(1, $pageCount)) {
            throw new StoryGenerationException(StoryGenerationException::INVALID_RESPONSE, 'Page count mismatch.');
        }

        // A túl hosszú címet levágjuk, nem dobjuk el a mesét (Backend Schema 3.5).
        // An overly long title is truncated rather than rejecting the story (Backend Schema 3.5).
        return ['title' => Str::limit($title, self::TITLE_MAX - 1, '…'), 'pages' => $pages];
    }
}
