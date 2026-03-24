<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Schema;

class ArticlesController extends Controller
{
    public function index(): View
    {
        $perPage = 25;

        $query = Article::query()
            ->where('is_published', true);

        if (Schema::hasColumn('articles', 'published_at')) {
            $query->orderByRaw('COALESCE(published_at, created_at) DESC');
        } else {
            $query->orderByDesc('created_at');
        }

        $articles = $query->paginate($perPage);

        return view()->first(
            [get_setting('theme') . '.articles', 'default.articles'],
            compact('articles')
        );
    }

    public function show(Article $article): View
    {
        if (! $article->is_published) {
            abort(404);
        }

        return view()->first(
            [get_setting('theme') . '.article', 'default.article'],
            compact('article')
        );
    }
}