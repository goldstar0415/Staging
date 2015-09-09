<?php

namespace App\Http\Controllers;

use App\Blog;
use App\BlogCategory;
use App\Http\Requests\Blog\BlogCategoryRequest;
use App\Http\Requests\Blog\BlogDestroyRequest;
use App\Http\Requests\Blog\BlogRequest;
use App\Http\Requests\Blog\BlogStoreRequest;
use App\Http\Requests\PaginateRequest;
use ChrisKonnertz\OpenGraph\OpenGraph;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

class BlogController extends Controller
{
    /**
     * BlogController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['index', 'show', 'preview']]);
        $this->middleware('blogger', ['only' => ['store', 'update', 'destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @param PaginateRequest $request
     */
    public function index(PaginateRequest $request)
    {
        $blog_posts = $request->has('user_id') ? Blog::where('user_id', $request->input('user_id')) : Blog::query();
        return $this->paginatealbe($request, $blog_posts);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param BlogStoreRequest $request
     * @return Blog
     */
    public function store(BlogStoreRequest $request)
    {
        $blog = new Blog($request->only(['blog_category_id', 'title', 'body', 'slug']));

        if ($request->hasFile('cover')) {
            $blog->cover = $request->file('cover');
        }
        if ($request->has('location')) {
            $blog->location = $request->input('location');
            $blog->address = $request->input('address');
        }

        $request->user()->blogs()->save($blog);

        return $blog;
    }

    /**
     * Display the specified resource.
     *
     * @param  Blog  $blog
     * @return Blog
     */
    public function show($blog)
    {
        ++$blog->count_views;
        $blog->save();

        return $blog;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param BlogRequest $request
     * @param  Blog $blog
     * @return Response
     */
    public function update(BlogRequest $request, $blog)
    {
        $blog->fill($request->only(['blog_category_id', 'title', 'body', 'slug']));

        if ($request->hasFile('cover')) {
            $blog->cover = $request->file('cover');
        }
        if ($request->has('location')) {
            $blog->location = $request->input('location');
            $blog->address = $request->input('address');
        }

        $blog->save();

        return $blog;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param BlogDestroyRequest $request
     * @param Blog $blog
     * @return array
     */
    public function destroy(BlogDestroyRequest $request, $blog)
    {
        return ['result' => $blog->delete()];
    }

    public function preview($blog)
    {
        $og = new OpenGraph();

        return view('opengraph')->with(
            'og',
            $og->title($blog->title)
                ->image($blog->cover->url())//TODO: change image
                ->description($blog->description)
                ->url(config('app.frontend_url') . '/blog/' . $blog->id)
        );
    }

    public function categories()
    {
        return BlogCategory::all();
    }

    public function popular(BlogCategoryRequest $request)
    {
        if ($request->has('category')) {
            $top_blogs = BlogCategory::find($request->get('category'))->blogs();
        } else {
            $top_blogs = Blog::query();
        }

        return $top_blogs->orderBy('count_views')->take(3)->get();
    }
}
