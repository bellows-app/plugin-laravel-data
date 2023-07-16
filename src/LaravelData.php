<?php

namespace Bellows\Plugins;

use Bellows\PluginSdk\Contracts\Installable;
use Bellows\PluginSdk\Facades\Console;
use Bellows\PluginSdk\Facades\Project;
use Bellows\PluginSdk\Facades\Vite;
use Bellows\PluginSdk\Plugin;
use Bellows\PluginSdk\PluginResults\CanBeInstalled;
use Bellows\PluginSdk\PluginResults\InstallationResult;

class LaravelData extends Plugin implements Installable
{
    use CanBeInstalled;

    public function install(): ?InstallationResult
    {
        $result = InstallationResult::create();

        if (Console::confirm('Do you want to transform DTOs to TypeScript?')) {
            $result->composerPackage('spatie/laravel-typescript-transformer')
                ->publishTag('typescript-transformer-config')
                ->npmDevPackage('vite-plugin-watch')
                ->wrapUp($this->installationWrapUpForTypeScript(...));
        }

        return $result;
    }

    public function requiredComposerPackages(): array
    {
        return [
            'spatie/laravel-data',
        ];
    }

    protected function installationWrapUpForTypeScript(): void
    {
        if (!Project::file('resources/types')->isDirectory()) {
            Project::file('resources/types')->makeDirectory();
        }

        Project::file('vite.config.js')->addJsImport("import { watch } from 'vite-plugin-watch'");

        Vite::addPlugin(<<<'PLUGIN'
        watch({
            pattern: 'app/{Data,Enums}/**/*.php',
            command: 'php artisan typescript:transform',
        }),
        PLUGIN);
    }
}
