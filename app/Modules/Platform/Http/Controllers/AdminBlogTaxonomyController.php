<?php

namespace App\Modules\Platform\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Platform\Models\BlogCategory;
use App\Modules\Platform\Models\BlogTag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AdminBlogTaxonomyController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Blog/TaxonomyManager', [
            'categories' => BlogCategory::withCount('posts')->orderBy('name')->get(),
            'tags' => BlogTag::withCount('posts')->orderBy('name')->get(),
        ]);
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $this->store($request, BlogCategory::class, true);
        return back()->with('status', "Categor\u{00ed}a creada.");
    }

    public function storeTag(Request $request): RedirectResponse
    {
        $this->store($request, BlogTag::class, false);
        return back()->with('status', 'Etiqueta creada.');
    }

    private function store(Request $request, string $model, bool $withDescription): void
    {
        $rules = [
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:100', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique((new $model)->getTable(), 'slug')],
        ];
        if ($withDescription) $rules['description'] = ['nullable', 'string', 'max:500'];
        $data = $request->validate($rules);

        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        if ($data['slug'] === '') $data['slug'] = 'tema';
        $model::create($data);
    }
}
