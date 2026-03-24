<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    /**
     * Display the home page specified in settings.
     */
    public function show(): View
    {
        $homePageId = get_setting('home_page');

        if (! $homePageId) {
            return view('frontend.index')->withMessage(
                '<p>No home page is currently selected for your website/application.</p>
                 <p>Please select a <strong>Home Page</strong> in the <strong>Settings</strong> section of the admin.</p>'
            );
        }

        $page = Page::query()
            ->with(['slider.slides' => fn ($q) => $q->orderBy('position')])
            ->find($homePageId);

        if (! $page) {
            return view('frontend.index')->withMessage(
                '<p>The selected <strong>Home Page</strong> could not be found.</p>
                 <p>Please choose a different page in <strong>Settings</strong>.</p>'
            );
        }

        $slides = $page->slider?->slides ?? collect();

        return view()->first(
            [get_theme() . '.page', 'default.page'],
            compact('page', 'slides'),
        );
    }
}