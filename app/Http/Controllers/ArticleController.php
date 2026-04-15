<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ArticleController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin');
    }

    public function index()
    {
        $articles = Article::with('user')->latest()->get();
        return view('articles.index', compact('articles'));
    }

    public function publicIndex()
    {
        $articles = Article::published()
            ->with('user')
            ->latest('published_at')
            ->latest()
            ->paginate(9);

        return view('articles.public.index', compact('articles'));
    }

    public function publicShow(Article $article)
    {
        if (! $article->is_published) {
            abort(404);
        }

        $article->loadMissing('user');

        $relatedArticles = Article::published()
            ->where('id', '!=', $article->id)
            ->with('user')
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('articles.public.show', compact('article', 'relatedArticles'));
    }

    public function create()
    {
        return view('articles.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'content' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_published' => 'nullable|boolean',
        ]);

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('articles', 'public');
            $validated['image'] = $imagePath;
        }

        $article = new Article($validated);
        $article->user_id = auth()->id();
        $article->is_published = $request->boolean('is_published');
        $article->published_at = $article->is_published ? now() : null;
        $article->save();

        return redirect()->route('articles.index')->with('success', 'Article created successfully.');
    }

    public function edit(Article $article)
    {
        return view('articles.edit', compact('article'));
    }

    public function update(Request $request, Article $article)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'content' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_published' => 'nullable|boolean',
        ]);

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($article->image) {
                Storage::disk('public')->delete($article->image);
            }
            $imagePath = $request->file('image')->store('articles', 'public');
            $validated['image'] = $imagePath;
        }

        $validated['is_published'] = $request->boolean('is_published');
        if ($validated['is_published'] && ! $article->published_at) {
            $validated['published_at'] = now();
        }
        if (! $validated['is_published']) {
            $validated['published_at'] = null;
        }

        $article->update($validated);

        return redirect()->route('articles.index')->with('success', 'Article updated successfully.');
    }

    public function destroy(Article $article)
    {
        if ($article->image) {
            Storage::disk('public')->delete($article->image);
        }
        
        $article->delete();
        return redirect()->route('articles.index')->with('success', 'Article deleted successfully.');
    }
}