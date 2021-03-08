<?php

namespace App\Http\Controllers\V1\Client;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Models\V1\Review;
use App\Models\V1\Transaction;
use DB;
use Exception;
use App\Http\Response\Client\ReviewTransformer;
use App\Http\Requests\Client\Review\CreateRequest;

class ReviewController extends Controller
{
    private $folder_upload = 'public/review/';

    public function getList(Request $request, $id = NULL)
    {
        try {
            $user = auth()->user();
            $per_page = $request->input('per_page', 10);
            $page = $request->input('page', 1);
            $order_by = $request->input('order_by', 'created_at');
            $sort = $request->input('sort', 'asc');

            $data = Review::where('user_id', $user->id);
            if ($id !== NULL) {
                $data = $data->with('image')->where('id', $id)->firstOrFail();
                // dd($data);
                return ReviewTransformer::detail($data);
            } else {
                $data = $data->orderBy($order_by, $sort)->paginate($per_page);
                return ReviewTransformer::list($data, true);
            }
            // return
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function create(CreateRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = auth()->user();
            $transaction = Transaction::where('transaction_code', $request->transaction_code)->whereHas('detail', function ($q) use ($request) {
                $q->where('id', $request->detail_id);
            })->with(['detail' => function ($q) use ($request) {
                $q->where('id', $request->detail_id);
            }])->firstOrFail();
            $review = Review::create([
                'user_id' => $user->id,
                'transaction_id' => $transaction->id,
                'transaction_detail_id' => $request->detail_id,
                'product_id' => $transaction->detail[0]->product_id,
                'type_id' => $transaction->detail[0]->type_id,
                'rating' => $request->rating,
                'review' => $request->review
            ]);
            foreach ($request->image as $img) {
                if (strpos($img, 'http://') === false && strpos($img, 'https://') === false) {
                    $img_r = imageUpload($this->folder_upload, @file_get_contents($img));
                } else {
                    $img_r = imageUpload($this->folder_upload, $img);
                }

                $review->image()->create([
                    'url' => $img_r,
                    'relation_to' => 'review'
                ]);
            }
            // CALCULATE RATING
            $rate_product = Review::where('product_id', $review->product_id)->get()->avg('rating');
            $review->product->update([
                'rating' => $rate_product
            ]);
            DB::commit();
            return ReviewTransformer::detail($review);
        } catch (Exception $e) {
            DB::rollback();
            throw new Exception($e->getMessage());
        }
    }

    public function update(CreateRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $user = auth()->user();
            $review = Review::where('id', $id)->firstOrFail();
            $review->update([
                'rating' => $request->rating,
                'review' => $request->review
            ]);

            $img = $review->image;
            $count = count($request->image);
            if (count($img) > $count) {
                $count = count($img);
            }
            $deleted_image = [];
            for ($i = 0; $i < $count; $i++) {
                if (isset($img[$i]) && isset($request->image[$i])) {
                    $m_image = $img[$i]->url;
                    if (strpos($request->image[$i], 'http://') !== false || strpos($request->image[$i], 'https://') !== false) {
                        $fn_img = str_replace(url('/') . '/', '', $request->image[$i]);
                        if ($img[$i]->url != $fn_img) {
                            $m_image = imageUpload($this->folder_upload, @file_get_contents($request->image[$i]));
                            $deleted_image[] = str_replace('storage', 'public', $img[$i]->url);
                        }
                    } else {
                        $m_image = imageUpload($this->folder_upload, $request->image[$i]);
                        $deleted_image[] = str_replace('storage', 'public', $img[$i]->url);
                    }
                    $img[$i]->update([
                        'url' => $m_image
                    ]);
                } else if (!isset($img[$i]) && isset($request->image[$i])) {
                    if (strpos($img, 'http://') === false && strpos($img, 'https://') === false) {
                        $img_r = imageUpload($this->folder_upload, @file_get_contents($request->image[$i]));
                    } else {
                        $img_r = imageUpload($this->folder_upload, $request->image[$i]);
                    }
                    $img[$i]->update([
                        'url' => $img_r
                    ]);
                } else if (isset($img[$i]) && !isset($request->image[$i])) {
                    $deleted_image[] = str_replace('storage', 'public', $img[$i]->url);
                    $img[$i]->delete();
                }
            }
            // CALCULATE RATING
            $rate_product = Review::where('product_id', $review->product_id)->get()->avg('rating');
            $review->product->update([
                'rating' => $rate_product
            ]);
            DB::commit();
            foreach ($deleted_image as $dim) {
                Storage::delete($dim);
            }
            return ReviewTransformer::detail($review->fresh());
        } catch (Exception $e) {
            DB::rollback();
            throw new Exception($e->getMessage());
        }
    }
}
