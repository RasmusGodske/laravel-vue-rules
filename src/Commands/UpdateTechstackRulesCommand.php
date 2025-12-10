<?php

declare(strict_types=1);

namespace RasmusGodske\LaravelVueRules\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use RasmusGodske\LaravelVueRules\TechstackRulesServiceProvider;

class UpdateTechstackRulesCommand extends Command
{
    protected $signature = 'techstack-rules:update
                            {--path=.claude/rules/techstack : The path where rules should be installed}
                            {--force : Overwrite existing rules}';

    protected $description = 'Install or update Claude Code techstack rules';

    public function __construct(
        private readonly Filesystem $filesystem,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $targetPath = $this->getTargetPath();
        $sourcePath = TechstackRulesServiceProvider::getRulesPath();

        if (! $this->filesystem->isDirectory($sourcePath)) {
            $this->error('Source rules directory not found. Package may be corrupted.');

            return self::FAILURE;
        }

        if ($this->filesystem->isDirectory($targetPath) && ! $this->option('force')) {
            $this->error("Rules directory already exists at: {$targetPath}");
            $this->line('');
            $this->line('Use --force to overwrite existing rules:');
            $this->line("  php artisan techstack-rules:update --force");
            $this->line('');
            $this->warn('Warning: Using --force will overwrite any customizations you have made.');

            return self::FAILURE;
        }

        $this->copyRules($sourcePath, $targetPath);

        return self::SUCCESS;
    }

    private function getTargetPath(): string
    {
        $path = $this->option('path');

        if (str_starts_with($path, '/')) {
            return $path;
        }

        return base_path($path);
    }

    private function copyRules(string $sourcePath, string $targetPath): void
    {
        if ($this->filesystem->isDirectory($targetPath)) {
            $this->filesystem->deleteDirectory($targetPath);
            $this->info('Removed existing rules directory.');
        }

        $this->filesystem->ensureDirectoryExists(dirname($targetPath));
        $this->filesystem->copyDirectory($sourcePath, $targetPath);

        $copiedFiles = $this->countFiles($targetPath);

        $this->newLine();
        $this->info("Successfully installed {$copiedFiles} rule files to: {$targetPath}");
        $this->newLine();
        $this->line('Your Claude Code rules are ready to use!');
    }

    private function countFiles(string $directory): int
    {
        $count = 0;

        foreach ($this->filesystem->allFiles($directory) as $file) {
            if ($file->getExtension() === 'md') {
                $count++;
            }
        }

        return $count;
    }
}
