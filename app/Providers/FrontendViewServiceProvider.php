<?php

namespace App\Providers;

use App\Models\SocialMedia;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class FrontendViewServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            return;
        }

        View::composer('*', function ($view) {
            try {
                // Skip if this is an admin request or Livewire component
                if (request()->is('admin') || 
                    request()->is('admin/*') || 
                    request()->is('livewire/*') ||
                    str_starts_with($view->name(), 'filament.')) {
                    return;
                }
                
                // Social media links (cached)
                $social = Cache::remember('social_media_links', now()->addMinutes(30), function () {
                    return SocialMedia::query()
                        ->orderBy('position')
                        ->get();
                });

                $view->with('social_media', $social);
                
                // SEO data - detect which model is present
                $data = $view->getData();
                $seoModel = $data['page'] ?? $data['article'] ?? $data['event'] ?? null;
                
                // Make sure we have an object with the HasSeo trait
                if (is_object($seoModel) && method_exists($seoModel, 'getSeoTitle')) {
                    $view->with('seo', $seoModel);
                } else {
                    // Fallback empty object for non-content pages (home, about, etc.)
                    $view->with('seo', (object) [
                        'getSeoTitle' => fn() => config('app.name'),
                        'getSeoDescription' => fn() => null,
                        'meta_keywords' => null,
                        'shouldIndex' => fn() => true,
                    ]);
                }
            } catch (\Throwable $e) {
                // Silently fail for admin/Livewire views
                // This prevents errors during Filament operations
                return;
            }
        });
    }
}