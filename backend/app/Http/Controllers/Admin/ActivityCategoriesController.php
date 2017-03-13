<?php

namespace App\Http\Controllers\Admin;

use App\ActivityCategory;
use App\Http\Requests\Admin\ActivityCategoryRequest;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class ActivityCategoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.activity_category.index')->with('categories', ActivityCategory::query()->paginate());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.activity_category.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ActivityCategoryRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(ActivityCategoryRequest $request)
    {
        ActivityCategory::create($request->all());

        return redirect()->route('admin.activity-categories.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\SpotTypeCategory  $category
     * @return \Illuminate\Http\Response
     */
    public function edit($category)
    {
        return view('admin.activity_category.edit')->with('category', $category);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param ActivityCategoryRequest $request
     * @param  \App\SpotTypeCategory $category
     * @return \Illuminate\Http\Response
     */
    public function update(ActivityCategoryRequest $request, $category)
    {
        $category->update($request->all());

        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ActivityCategory  $category
     * @return \Illuminate\Http\Response
     */
    public function destroy($category)
    {
        if ($category->activities()->count() > 0) {
            return back()->withErrors(['Some activities attached to this category!']);
        }

        $category->delete();

        return back();
    }
}
