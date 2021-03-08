<?php

namespace App\Http\Controllers\V1\Client;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Response\Client\BlogTransformer;

use App\Models\V1\Blog;

class BlogController extends Controller
{
    public function getList(Request $request)
    {
        try {
            $per_page = $request->input('per_page', 10);
            $data = Blog::where('status', 'publish');
            $order = 'DESC';
            $sort_by = 'created_at';


            if ($request->filled('title')) {
                $data = $data->where('title', 'ilike', '%' . $request->title . '%');
            }

            if ($request->filled('tag')) {
                $data = $data->whereHas('tag', function ($q) use ($request) {
                    $tag = explode(',', $request->tag);
                    $q->whereIn('slug', $tag);
                });
            }

            if ($request->filled('category')) {
                $data = $data->whereHas('category', function ($q) use ($request) {
                    $category = explode(',', $request->category);
                    $q->whereIn('slug', $tag);
                });
            }

            $data = $data->orderBy($sort_by, $order)->paginate($per_page);
            return BlogTransformer::getList($data);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function getDetail($slug)
    {
        try {
            $data = Blog::where([
                'status' => 'publish',
                'slug' => $slug
            ])->firstOrFail();
            return BlogTransformer::getDetail($data);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
