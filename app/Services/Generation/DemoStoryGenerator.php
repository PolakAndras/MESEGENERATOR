<?php

namespace App\Services\Generation;

use App\Contracts\StoryTextGenerator;
use Illuminate\Support\Str;

/**
 * Bemutató mód: kézzel írt mondatsablonokból állít össze mesét, AI-hívás nélkül (Tech Stack 5.1, IP D-04).
 * Így a teljes folyamat kulcs és kvóta nélkül is kipróbálható, és élesben tartalékként is működik (PRD R-01).
 * A választ ugyanabban a JSON-formában adja, mint egy valódi modell, így ugyanaz az ellenőrzés fut rajta.
 * A sablonok véletlenszerűen váltakoznak, hogy az „Új szöveg” gomb (US-09) itt is mást adjon.
 *
 * Demo mode: builds a story from hand-written sentence templates, without an AI call (Tech Stack 5.1, IP D-04).
 * The whole flow can be tried without a key or quota, and it also serves as a production fallback (PRD R-01).
 * It answers in the same JSON shape as a real model, so the same validation runs on it.
 * Templates vary randomly so that "New text" (US-09) gives something different here too.
 */
class DemoStoryGenerator implements StoryTextGenerator
{
    private const TITLES = [
        ':name és :place',
        ':name esti kalandja',
        ':name és a csillagfényes nap',
    ];

    private const OPENINGS = [
        'Egyszer volt, hol nem volt, élt egyszer egy :age éves gyerek, akit úgy hívtak: :name.',
        'Ezen az estén :name különösen kíváncsi volt, mi vár rá a mesében.',
        'Messze, a hetedik dombon is túl, egy kis házban élt :name.',
    ];

    // A :group a gyerek és az állatok felsorolása („Panni, a róka és a medve”). / :group lists the child and the animals.
    private const WITH_FRIENDS = [
        ':place_cap közepén :group vidáman töltötték az időt.',
        ':place_cap csendjében :name új barátokra lelt: :friends.',
        ':group együtt indultak felfedezőútra, és :place tele volt meglepetéssel.',
    ];

    private const CHILD_ONLY = [
        ':name egyedül sétált, és közben dúdolt egy kedves dalt. :place_cap barátságosan fogadta.',
        ':place_cap mesebeli fényben ragyogott, :name pedig mosolyogva nézett körül.',
    ];

    private const FRIENDS_ONLY = [
        ':place_cap mélyén eközben :friends is készülődtek: tudták, hogy hamarosan vendég érkezik.',
        'Míg mindenki pihent, :friends halkan beszélgettek. :place_cap csendesen figyelt.',
    ];

    private const QUIET = [
        ':place_cap csendes volt és nyugodt, mintha maga is az estére várna.',
        'A szél lágyan suhogott, :place_cap pedig lassan elcsendesedett.',
    ];

    private const ACTION = ':who közben vidáman :action.';

    private const ENDINGS = [
        'Aztán a csillagok kigyúltak az égen, és :name boldogan, mosolyogva aludt el. Vége.',
        'Ez volt :name legszebb napja, és már alig várta a következő mesét. Jó éjszakát.',
    ];

    public function generate(array $story): string
    {
        $name = $story['child']['name'];
        $age = $story['child']['age'];
        $pages = [];

        foreach ($story['pages'] as $i => $page) {
            $animals = $this->animalNames($page['animals']);
            $friends = $this->listing($animals);
            $vars = ['name' => $name, 'age' => $age, 'friends' => $friends, 'group' => $this->listing(array_merge([$name], $animals))]
                + $this->place($page['background']);
            $sentences = [];

            if ($i === 0) {
                $sentences[] = $this->pick(self::OPENINGS, $vars);
            }
            if ($page['child_action'] !== null && $friends !== '') {
                $sentences[] = $this->pick(self::WITH_FRIENDS, $vars);
            } elseif ($page['child_action'] !== null) {
                $sentences[] = $this->pick(self::CHILD_ONLY, $vars);
            } elseif ($friends !== '') {
                $sentences[] = $this->pick(self::FRIENDS_ONLY, $vars);
            } else {
                $sentences[] = $this->pick(self::QUIET, $vars);
            }
            if ($page['child_action'] !== null && $page['child_action'] !== 'áll' && $age >= 5) {
                $sentences[] = strtr(self::ACTION, [':who' => $name, ':action' => $page['child_action']]);
            }
            if ($i === count($story['pages']) - 1) {
                $sentences[] = $this->pick(self::ENDINGS, $vars);
            }

            $pages[] = ['page' => $page['page'], 'text' => implode(' ', $sentences)];
        }

        $first = $this->place($story['pages'][0]['background']);
        $title = $this->pick(self::TITLES, ['name' => $name] + $first);

        return json_encode(['title' => $title, 'pages' => $pages], JSON_UNESCAPED_UNICODE);
    }

    private function pick(array $templates, array $vars): string
    {
        $replace = [];
        foreach ($vars as $key => $value) {
            $replace[':'.$key] = (string) $value;
        }
        // Hosszabb kulcs előbb, hogy a :place ne írja felül a :place_cap elejét.
        // Longer keys first, so :place does not clobber the start of :place_cap.
        uksort($replace, fn ($a, $b) => strlen($b) <=> strlen($a));

        return strtr($templates[array_rand($templates)], $replace);
    }

    // A háttér neve névelővel, toldalék nélkül: a magyar ragozás sablonból nem képezhető biztonságosan.
    // The background name with its article, no suffix: Hungarian inflection is not safe to template.
    private function place(string $background): array
    {
        $noun = Str::lower($background);
        $withArticle = $this->article($noun).' '.$noun;

        return ['place' => $withArticle, 'place_cap' => Str::ucfirst($withArticle)];
    }

    private function animalNames(array $animals): array
    {
        return array_map(fn ($a) => $this->article(Str::lower($a['name'])).' '.Str::lower($a['name']), $animals);
    }

    // Magyar felsorolás: „a, b és c”. / Hungarian listing: "a, b és c".
    private function listing(array $names): string
    {
        if (count($names) <= 1) {
            return $names[0] ?? '';
        }
        $last = array_pop($names);

        return implode(', ', $names).' és '.$last;
    }

    private function article(string $word): string
    {
        return preg_match('/^[aáeéiíoóöőuúüű]/u', $word) ? 'az' : 'a';
    }
}
