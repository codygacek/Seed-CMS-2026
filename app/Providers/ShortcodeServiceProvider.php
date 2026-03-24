<?php

namespace App\Providers;

use App\Services\ShortcodeParser;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class ShortcodeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ShortcodeParser::class, function ($app) {
            return new ShortcodeParser();
        });
    }

    public function boot(): void
    {
        // Register Blade directive
        Blade::directive('shortcodes', function ($expression) {
            return "<?php echo app(App\Services\ShortcodeParser::class)->parse($expression); ?>";
        });
    }
}