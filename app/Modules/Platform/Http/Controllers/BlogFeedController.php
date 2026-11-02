<?php

namespace App\Modules\Platform\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Platform\Models\BlogPost;
use Illuminate\Http\Response;

class BlogFeedController extends Controller
{
    public function sitemap(): Response
    {
        return response()->view('blog.sitemap', [
            'posts' => BlogPost::published()->newest()->get(['slug', 'updated_at']),
        ], 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function rss(): Response
    {
        return response()->view('blog.rss', [
            'posts' => BlogPost::published()->with('author:id,name')->newest()->limit(30)->get(),
        ], 200, ['Content-Type' => 'application/rss+xml; charset=UTF-8']);
    }
}
