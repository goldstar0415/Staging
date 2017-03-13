<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\SpotCategory\SpotCategoryRequest;
use App\SpotTypeCategory;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class SpotCategoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.spot_categories.index')->with('categories', SpotTypeCategory::query()->paginate());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.spot_categories.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param SpotCategoryRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(SpotCategoryRequest $request)
    {
        $category = new SpotTypeCategory($request->all());
        $category->type()->associate($request->spot_type_id);
        $category->save();

        return redirect()->route('admin.spot-categories.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\SpotTypeCategory  $category
     * @return \Illuminate\Http\Response
     */
    public function edit($category)
    {
        return view('admin.spot_categories.edit')->with('category', $category);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param SpotCategoryRequest $request
     * @param  \App\SpotTypeCategory $category
     * @return \Illuminate\Http\Response
     */
    public function update(SpotCategoryRequest $request, $category)
    {
        $category->fill($request->except('spot_type_id'));
        $category->type()->associate($request->spot_type_id);
        $category->save();

        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\SpotTypeCategory  $category
     * @return \Illuminate\Http\Response
     */
    public function destroy($category)
    {
        if ($category->spots()->withoutNewest()->withRequested()->count() > 0) {
            return back()->withErrors(['Some spots attached to this category!']);
        }

        $category->delete();

        return back();
    }
}
