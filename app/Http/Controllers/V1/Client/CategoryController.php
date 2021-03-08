<?php

namespace App\Http\Controllers\V1\Client;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Response\Client\CategoryTransformer;
use App\Models\V1\Category;

class CategoryController extends Controller
{
    public function getList(Request $request)
    {
        try {
            $data = Category::where('parent_id', 0)->has('children')->with('children');
            $sort_by = 'order';
            $order = 'asc';
            $data  = $data->orderBy($sort_by, $order)->get();
            return CategoryTransformer::getList($data);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function getDetail(Request $request, $slug)
    {
        try {
            $data = Category::where('parent_id', '!=', 0)->where('slug', $slug)->with('children')->firstOrFail();
            return CategoryTransformer::getDetail($data);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
