<?php

namespace App\Http\Controllers\Admin;

use App\BlogCategory;
use App\Http\Requests\Admin\BlogCategoryRequest;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class BlogCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.blog_category.index')->with('categories', BlogCategory::query()->paginate());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.blog_category.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param BlogCategoryRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(BlogCategoryRequest $request)
    {
        BlogCategory::create($request->all());

        return redirect()->route('admin.blog-categories.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\BlogCategory  $category
     * @return \Illuminate\Http\Response
     */
    public function edit($category)
    {
        return view('admin.blog_category.edit')->with('category', $category);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param BlogCategoryRequest $request
     * @param  \App\BlogCategory $category
     * @return \Illuminate\Http\Response
     */
    public function update(BlogCategoryRequest $request, $category)
    {
        $category->update($request->all());

        return redirect()->route('admin.blog-categories.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\BlogCategory  $category
     * @return \Illuminate\Http\Response
     */
    public function destroy($category)
    {
        if ($category->blogs()->withoutNewest()->count() > 0) {
            return back()->withErrors(['Some blog posts attached to this category!']);
        }

        $category->delete();

        return back();
    }
}
