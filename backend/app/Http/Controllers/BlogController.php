<?php

namespace App\Http\Controllers;

use App\Blog;
use App\BlogCategory;
use App\BloggerRequest as BloggerRequestModel;
use App\Http\Requests\Blog\BlogCategoryRequest;
use App\Http\Requests\Blog\BlogDestroyRequest;
use App\Http\Requests\Blog\BloggerRequest;
use App\Http\Requests\Blog\BlogImageUploadRequest;
use App\Http\Requests\Blog\BlogRequest;
use App\Http\Requests\Blog\BlogStoreRequest;
use App\Http\Requests\Blog\BlogUpdateRequest;
use App\Http\Requests\PaginateRequest;
use ChrisKonnertz\OpenGraph\OpenGraph;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

/**
 * Class BlogController
 * @package App\Http\Controllers
 *
 * Blog resource controller
 */
class BlogController extends Controller
{
    /**
     * BlogController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['index', 'show', 'preview', 'popular', 'categories']]);
        //$this->middleware('blogger', ['only' => ['store']]);
        //$this->middleware('bloggerOrAdmin', ['only' => ['update', 'destroy', 'upload']]);
    }

    /**
     * Display a listing of the blogs.
     *
     * @param PaginateRequest $request
     * @return mixed
     */
    public function index(PaginateRequest $request)
    {
        $blog_posts = $request->has('user_id') ? Blog::where('user_id', $request->input('user_id')) : Blog::query();
        return $this->paginatealbe($request, $blog_posts->with(['category', 'user', 'spot']));
    }

    /**
     * Store a newly created blog post in storage.
     *
     * @param BlogStoreRequest $request
     * @return Blog
     */
    public function store(BlogStoreRequest $request)
    {
        $blog = new Blog();
        $this->parseRequestBlog($request, $blog);
        $request->user()->blogs()->save($blog);

        return $blog;
    }

    /**
     * Display the specified blog post.
     *
     * @param  Blog  $blog
     * @return Blog
     */
    public function show($blog)
    {
        ++$blog->count_views;
        $blog->save();

        return $blog->load(['category', 'user', 'spot']);
    }

    /**
     * Update the specified blog post in storage.
     *
     * @param BlogUpdateRequest $request
     * @param  Blog $blog
     * @return Blog
     */
    public function update(BlogUpdateRequest $request, $blog)
    {
        $this->parseRequestBlog($request, $blog);
        $blog->save();

        return $blog;
    }

    /**
     * Parse blog properties from request
     * @param Request $request
     * @param Blog $blog
     */
    private function parseRequestBlog($request, $blog)
    {
        $blog->fill($request->only(['blog_category_id', 'title', 'body']));

        if ($request->hasFile('cover')) {
            $blog->cover = $request->file('cover');
        }

        if ($request->has('location')) {
            $blog->location = $request->input('location');
            $blog->address = $request->input('address');
        }
        
        $blog->spot_id = ($request->has('spot_id')) ? $request->input('spot_id') : null;

        $slug = str_slug($blog->title);
        $validator = \Validator::make(compact('slug'), ['slug' => 'required|alpha_dash|max:255|unique:blogs']);
        $blog->slug = !$validator->fails() && !is_numeric($slug) ? $slug: null;
    }

    /**
     * Remove the specified blog post from storage.
     *
     * @param BlogDestroyRequest $request
     * @param Blog $blog
     * @return array
     */
    public function destroy(BlogDestroyRequest $request, $blog)
    {
        return ['result' => $blog->delete()];
    }

    /**
     * The specify blog post preview
     *
     * @param $blog
     * @return $this
     * @throws \Exception
     */
    public function preview($blog)
    {
        $og = new OpenGraph();

        return view('opengraph')->with(
            'og',
            $og->title($blog->title)
                ->image($blog->cover->url())
                ->description($blog->body)
                ->url(frontend_url($blog->user_id, 'article', $blog->slug))
        );
    }

    /**
     * Show all blog categories
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function categories()
    {
        return BlogCategory::all();
    }

    /**
     * Show popular blog posts
     *
     * @param BlogCategoryRequest $request
     * @return mixed
     */
    public function popular(BlogCategoryRequest $request)
    {
        if ($request->has('category')) {
            $top_blogs = BlogCategory::find($request->get('category'))->blogs();
        } else {
            $top_blogs = Blog::query();
        }

        return $top_blogs->withoutNewest()->orderBy('count_views', 'DESC')->take(3)->get();
    }

    /**
     * Sends new blogger request
     *
     * @param BloggerRequest $request
     * @return BloggerRequestModel
     */
    public function bloggerRequest(BloggerRequest $request)
    {
        $blogger_request = new BloggerRequestModel([
            'text' => $request->input('body'),
            'status' => 'requested'
        ]);

        $request->user()->bloggerRequest()->save($blogger_request);

        return $blogger_request;
    }

    /**
     * Upload images for blog posts
     * @param BlogImageUploadRequest $request
     * @return array
     */
    public function upload(BlogImageUploadRequest $request)
    {
        $image = $request->file('image');
        $name = str_random() . '.' . $image->guessExtension();
        $path = 'uploads/posts';

        $sizes = getimagesize($image->move(public_path($path), $name));

        return [
            'image_url' => url($path, $name),
            'image_size'=> [
                'width' => $sizes[0],
                'height' => $sizes[1]
            ]
        ];
    }
}
