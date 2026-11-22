<?php

namespace App\Providers;

use App\Contracts\StoryTextGenerator;
use App\Services\Generation\DemoStoryGenerator;
use App\Services\Generation\OpenAiCompatibleStoryGenerator;
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
        // Ismeretlen értéknél a demo marad, hogy hibás beállítás ne törje el az oldalt.
        // STORY_GENERATOR picks the implementation; the rest of the code only knows the interface (Tech Stack 5.1).
        // An unknown value falls back to demo, so a bad setting never breaks the site.
        $this->app->bind(StoryTextGenerator::class, function () {
            $name = config('story.generator');
            $provider = config("story.providers.$name");

            return $provider ? new OpenAiCompatibleStoryGenerator($provider) : new DemoStoryGenerator();
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
