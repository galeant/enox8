<?php

namespace App\Http\Controllers\V1\Client;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\V1\Product;
use App\Models\V1\Review;

use App\Http\Response\Client\CatalogTransformer;
use App\Http\Response\Client\ReviewTransformer;
// use App\Http\Response\CatalogResponseTransformer/;

class CatalogController extends Controller
{
    public function __construct(Request $request)
    {
        $header = $request->header('Authorization');
        if ($header) {
            $this->middleware('auth:api');
            $request->request->add([
                'auth' => true
            ]);
        }
    }

    public function getList(Request $request)
    {
        try {
            $featured = $request->input('featured', false);
            $recomend = $request->input('recomend', false);
            $page = $request->input('page', 1);
            $per_page = $request->input('per_page', 10);
            $sort_by = $request->input('sort_by', 'name');
            $order = $request->input('order', 'ASC');

            $data = Product::with('category', 'type', 'defaultType')
                ->where('status', 'publish')
                ->whereHas('defaultType', function ($q) {
                    $q->where('status', 'publish');
                })
                ->whereHas('store', function ($q) {
                    $q->where('deleted_at', NULL);
                })
                ->whereHas('category', function ($q) {
                    $q->where('status', 'publish');
                });

            if ($request->filled('category')) {
                $data = $data->whereHas('category', function ($q) use ($request) {
                    $q->where('slug', $request->category);
                });
            }

            if ($featured !== false) {
                $data = $data->where('is_featured', true);
            }
            if ($recomend !== false) {
                $data = $data->orderBy('rating', 'desc');
            }

            $data = $data->orderBy($sort_by, $order)->paginate($per_page);
            return CatalogTransformer::list($data, NULL, $request->auth);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function getDetail($slug, Request $request)
    {
        try {
            $data = Product::where('slug', $slug)->firstOrFail();
            $related_product = Product::whereHas('category', function ($q) use ($data) {
                $q->whereIn('id', $data->category->pluck('id'));
            })->where('slug', '!=', $slug)->limit(10)->get();
            return CatalogTransformer::getDetail($data, $related_product, $request->auth);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function getReview($slug, Request $request)
    {
        try {
            $per_page = $request->input('per_page', 10);
            $page = $request->input('page', 1);
            $order_by = $request->input('order_by', 'created_at');
            $sort = $request->input('sort', 'asc');

            $data = Review::whereHas('product', function ($q) use ($slug) {
                $q->where('slug', $slug);
            })->orderBy($order_by, $sort)->paginate($per_page);
            return ReviewTransformer::list($data);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
