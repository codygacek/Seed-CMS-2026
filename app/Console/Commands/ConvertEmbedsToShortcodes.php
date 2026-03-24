<?php

namespace App\Console\Commands;

use App\Models\Page;
use App\Models\Article;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ConvertEmbedsToShortcodes extends Command
{
    protected $signature = 'content:convert-embeds 
                            {--dry-run : Preview changes without saving}
                            {--model= : Specify model (Page or Article)}
                            {--id= : Convert specific record ID only}';

    protected $description = 'Convert embedded scripts and iframes to shortcodes';

    protected array $conversions = [];

    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $model = $this->option('model');
        $id = $this->option('id');

        $this->info($isDryRun ? '🔍 DRY RUN - No changes will be saved' : '⚡ LIVE RUN - Changes will be saved');
        $this->newLine();

        $models = $model ? [$model] : ['Page', 'Article'];

        foreach ($models as $modelName) {
            $this->processModel($modelName, $isDryRun, $id);
        }

        $this->displaySummary();

        if (!$isDryRun && $this->conversions) {
            $this->info('✅ Conversion complete!');
        }

        return Command::SUCCESS;
    }

    protected function processModel(string $modelName, bool $isDryRun, ?string $id): void
    {
        $modelClass = "App\\Models\\{$modelName}";
        
        if (!class_exists($modelClass)) {
            $this->warn("Model {$modelClass} not found, skipping...");
            return;
        }

        $query = $modelClass::query();
        
        if ($id) {
            $query->where('id', $id);
        }

        $records = $query->get();

        $this->info("Processing {$records->count()} {$modelName}(s)...");
        $this->newLine();

        foreach ($records as $record) {
            $this->processRecord($record, $isDryRun);
        }
    }

    protected function processRecord($record, bool $isDryRun): void
    {
        $originalContent = $record->content;
        $convertedContent = $originalContent;
        $changes = [];

        // Convert Wufoo embeds
        $convertedContent = $this->convertWufooEmbeds($convertedContent, $changes);

        // Convert iframes
        $convertedContent = $this->convertIframes($convertedContent, $changes);

        // Convert script tags
        $convertedContent = $this->convertScriptTags($convertedContent, $changes);

        // Check if any changes were made
        if ($originalContent === $convertedContent) {
            return; // No changes needed
        }

        $this->conversions[] = [
            'model' => class_basename($record),
            'id' => $record->id,
            'title' => $record->title ?? "ID: {$record->id}",
            'changes' => $changes,
        ];

        $this->info("📝 " . class_basename($record) . " #{$record->id}: {$record->title}");
        
        foreach ($changes as $change) {
            $this->line("   → {$change}");
        }

        if ($this->option('verbose')) {
            $this->line("   BEFORE:");
            $this->line("   " . substr($originalContent, 0, 200) . "...");
            $this->line("   AFTER:");
            $this->line("   " . substr($convertedContent, 0, 200) . "...");
        }

        $this->newLine();

        if (!$isDryRun) {
            $record->update(['content' => $convertedContent]);
        }
    }

    protected function convertWufooEmbeds(string $content, array &$changes): string
    {
        // Pattern: <div id="wufoo-FORMID"></div> followed by <script>...FORMID...</script>
        $pattern = '/<div\s+id="wufoo-([a-z0-9]+)"><\/div>\s*<script[^>]*>.*?var\s+\1;.*?<\/script>/is';
        
        $content = preg_replace_callback($pattern, function($matches) use (&$changes) {
            $formId = $matches[1];
            $changes[] = "Converted Wufoo form: {$formId}";
            return "[wufoo form=\"{$formId}\"]";
        }, $content);

        return $content;
    }

    protected function convertIframes(string $content, array &$changes): string
    {
        // Pattern: <iframe src="..." width="..." height="...">
        $pattern = '/<iframe\s+[^>]*src=["\']([^"\']+)["\'][^>]*>/i';
        
        $content = preg_replace_callback($pattern, function($matches) use (&$changes) {
            $fullTag = $matches[0];
            $src = $matches[1];
            
            // Extract width and height if present
            preg_match('/width=["\']([^"\']+)["\']/i', $fullTag, $widthMatch);
            preg_match('/height=["\']([^"\']+)["\']/i', $fullTag, $heightMatch);
            
            $width = $widthMatch[1] ?? '100%';
            $height = $heightMatch[1] ?? '400';
            
            $changes[] = "Converted iframe: " . substr($src, 0, 50) . "...";
            
            return "[iframe url=\"{$src}\" width=\"{$width}\" height=\"{$height}\"]";
        }, $content);

        return $content;
    }

    protected function convertScriptTags(string $content, array &$changes): string
    {
        // Pattern: <script src="..."></script>
        $pattern = '/<script\s+[^>]*src=["\']([^"\']+)["\'][^>]*><\/script>/i';
        
        $content = preg_replace_callback($pattern, function($matches) use (&$changes) {
            $src = $matches[1];
            $changes[] = "Converted script tag: " . substr($src, 0, 50) . "...";
            return "[script src=\"{$src}\"]";
        }, $content);

        return $content;
    }

    protected function displaySummary(): void
    {
        if (empty($this->conversions)) {
            $this->info('✨ No embeds found to convert!');
            return;
        }

        $this->newLine();
        $this->info('📊 SUMMARY:');
        $this->line(str_repeat('─', 50));
        
        $totalChanges = 0;
        $byModel = [];

        foreach ($this->conversions as $conversion) {
            $model = $conversion['model'];
            $byModel[$model] = ($byModel[$model] ?? 0) + 1;
            $totalChanges += count($conversion['changes']);
        }

        foreach ($byModel as $model => $count) {
            $this->line("  {$model}s converted: {$count}");
        }

        $this->line("  Total conversions: {$totalChanges}");
        $this->line(str_repeat('─', 50));
    }
}