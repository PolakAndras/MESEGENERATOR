<?php

namespace App\Providers;

use App\Contracts\StoryTextGenerator;
use App\Services\Generation\OpenAiCompatibleStoryGenerator;
use App\Services\Generation\TemplateStoryGenerator;
use App\Services\StoryDraftService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // A STORY_GENERATOR választja ki a megvalósítást; a kód többi része csak az interfészt ismeri (Tech Stack 5.1).
        // Alapból és ismeretlen értéknél a mesegyűjtemény fut (PRD US-24); AI csak groq / mistral beállítással.
        // STORY_GENERATOR picks the implementation; the rest of the code only knows the interface (Tech Stack 5.1).
        // By default and for unknown values the story collection runs (PRD US-24); AI only with groq / mistral.
        $this->app->bind(StoryTextGenerator::class, function () {
            $provider = config('story.providers.'.config('story.generator'));

            return $provider ? new OpenAiCompatibleStoryGenerator($provider) : new TemplateStoryGenerator();
        });

        $this->app->singleton(StoryDraftService::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
