<?php

namespace App\Http\Controllers\V1\Client;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\V1\Promo;
use App\Http\Response\Client\PromoTransformer;
use Carbon\Carbon;


class PromoController extends Controller
{
    public function getList(Request $request)
    {
        $now = Carbon::now();
        $data = Promo::where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->where('is_display', true)
            ->where('status', 'publish')
            ->get();
        return PromoTransformer::list($data);
    }

    public function getDetail($slug)
    {
        $now = Carbon::now();
        $data = Promo::where('slug', $slug)
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->where('is_display', true)
            ->where('status', 'publish')
            ->firstOrFail();
        return PromoTransformer::detail($data);
    }
}
