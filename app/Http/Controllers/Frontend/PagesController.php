<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;

class PagesController extends Controller
{
    public function show(Page $page, Request $request)
    {
        // Password / token-protected pages (backwards compatible session key)
        if ($page->token) {
            $authed = (array) $request->session()->get('authenticated.resources', []);

            if (! in_array($page->slug, $authed, true)) {
                $tokenable_id = $page->id;
                $tokenable_type = Page::class; // more correct than 'Page'

                return view()->first(
                    [get_theme() . '.resource-auth', 'default.resource-auth'],
                    compact('page', 'tokenable_id', 'tokenable_type')
                );
            }
        }

        // Slider/slides (if relationship exists)
        $slides = null;
        if ($page->relationLoaded('slider')) {
            $slides = optional($page->slider)->slides;
        } else {
            $slides = optional($page->slider)->slides;
        }

        return view()->first(
            [get_theme() . '.page', 'default.page'],
            compact('page', 'slides')
        );
    }
}