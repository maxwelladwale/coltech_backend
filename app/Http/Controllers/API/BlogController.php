<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BlogController extends Controller
{
    /**
     * Get published blog posts
     * 
     * GET /api/blog?tag=fleet&limit=10
     */
    public function index(Request $request): JsonResponse
    {
        $query = BlogPost::published();

        // Filter by tag
        if ($request->has('tag')) {
            $query->whereJsonContains('tags', $request->tag);
        }

        // Limit results
        $limit = $request->input('limit', 10);
        $posts = $query->orderBy('published_at', 'desc')
            ->take($limit)
            ->get();

        return response()->json($posts);
    }

    /**
     * Get single blog post by slug
     * 
     * GET /api/blog/{slug}
     */
    public function show(string $slug): JsonResponse
    {
        $post = BlogPost::where('slug', $slug)
            ->published()
            ->first();

        if (!$post) {
            return response()->json([
                'message' => 'Blog post not found'
            ], 404);
        }

        // Increment views
        $post->incrementViews();

        return response()->json($post);
    }

    /**
     * Get recent posts
     * 
     * GET /api/blog/recent?limit=5
     */
    public function recent(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 5);
        
        $posts = BlogPost::published()
            ->orderBy('published_at', 'desc')
            ->take($limit)
            ->get(['id', 'title', 'slug', 'excerpt', 'featured_image', 'published_at']);

        return response()->json($posts);
    }
}