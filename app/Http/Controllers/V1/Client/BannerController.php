<?php

namespace App\Http\Controllers\V1\Client;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\V1\Banner;
use App\Http\Response\Client\BannerTransformer;

class BannerController extends Controller
{
    public function getList(Request $request)
    {
        try {
            $data = Banner::with('webBanner', 'mobileBanner');
            $sort_by = $request->input('sort_by', 'order');
            $order = $request->input('order', 'ASC');
            $data = $data->orderBy($sort_by, $order)->get();
            return BannerTransformer::list($data);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function getDetail($slug)
    {
        try {
            $data = Banner::where('slug', $slug)->firstOrFail();
            return BannerTransformer::getDetail($data);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
