<?php

namespace App\Http\Controllers\Admin;

use App\Blog;
use App\Http\Requests\Admin\BlogRequest;
use App\Http\Requests\Admin\SearchRequest;
use App\Http\Requests\Blog\BlogStoreRequest;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class BlogController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.blog.index')->with('blogs', Blog::query()->paginate());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.blog.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  BlogRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(BlogRequest $request)
    {
        $blog = new Blog($request->only(['blog_category_id', 'title', 'body']));

        if ($request->has('slug')) {
            $blog->slug = $request->input('slug');
        } else {
            $slug = str_slug($blog->title);
            $validator = \Validator::make(compact('slug'), ['slug' => 'required|alpha_dash|max:255|unique:blogs']);
            if ($validator->fails()) {
                abort(422, $validator->messages()->get('slug')[0]);
            }

            $blog->slug = $slug;
        }

        if ($request->hasFile('cover')) {
            $blog->cover = $request->file('cover');
        }
        if ($request->has('location')) {
            $blog->location = $request->input('location');
            $blog->address = $request->input('address');
        }
        if ($request->has('main')) {
            $blog->main = (bool)$request->main;
        }

        $request->user()->blogs()->save($blog);

        return redirect()->route('admin.posts.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Blog  $blog
     * @return \Illuminate\Http\Response
     */
    public function edit($blog)
    {
        return view('admin.blog.edit')->with('blog', $blog);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  BlogRequest  $request
     * @param  \App\Blog  $blog
     * @return \Illuminate\Http\Response
     */
    public function update(BlogRequest $request, $blog)
    {
        $slug = '';

        $blog->fill($request->only(['blog_category_id', 'title', 'body']));

        if ($request->has('slug')) {
            $slug = $request->input('slug');
        } else {
            $slug = str_slug($blog->title);
        }

        if ($blog->slug !== $slug) {
            $validator = \Validator::make(compact('slug'), ['slug' => 'required|alpha_dash|unique:blogs']);
            if ($validator->fails()) {
                abort(422, $validator->messages()->get('slug')[0]);
            }

            $blog->slug = $slug;
        }

        if ($request->hasFile('cover')) {
            $blog->cover = $request->file('cover');
        }
        if ($request->has('location')) {
            $blog->location = $request->input('location');
            $blog->address = $request->input('address');
        }
        if ($request->has('main')) {
            $blog->main = (bool)$request->main;
        }

        $blog->save();

        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Blog  $blog
     * @return \Illuminate\Http\Response
     */
    public function destroy($blog)
    {
        $blog->delete();

        return back();
    }

    public function search(SearchRequest $request)
    {
        return view('admin.blog.index')->with('blogs', Blog::search($request->search_text)->paginate());
    }
}
