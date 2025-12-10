<?php

declare(strict_types=1);

namespace RasmusGodske\LaravelVueRules;

use Illuminate\Support\ServiceProvider;
use RasmusGodske\LaravelVueRules\Commands\UpdateTechstackRulesCommand;

class TechstackRulesServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                UpdateTechstackRulesCommand::class,
            ]);
        }
    }

    public static function getRulesPath(): string
    {
        return dirname(__DIR__) . '/rules';
    }
}
