<?php

namespace App\Http\Controllers\V1\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\V1\Type;
use App\Models\V1\Comment;
use App\Models\V1\Review;
use App\Models\V1\Transaction;

use DB;
use Exception;
use App\Http\Response\Dashboard\NotificationTransformer;

class NotificationController extends Controller
{
    private $per_page, $page, $order_by, $sort;

    public function __construct(Request $request)
    {
        $this->per_page = $request->input('per_page', 3);
        $this->page = $request->input('page', 1);
        $this->order_by = $request->input('order_by', 'id');
        $this->sort = $request->input('sort', 'DESC');
    }
    public function zeroStock(Request $request, Type $type)
    {
        try {
            $data = $type->with('product')
                // ->whereHas('product', function ($q) {
                //     $q->where('status','publish');
                // })
                ->where('stock', 0)
                ->orderBy($this->order_by, $this->sort)
                ->get();
            return NotificationTransformer::stockZero($data);
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function getNewComment(Request $request, Comment $comment)
    {
        try {
            $data = $comment->with('product')
                ->where('view', false)
                ->has('product')
                ->orderBy('updated_at', 'desc')
                ->limit(3)
                ->get();
            return NotificationTransformer::newComment($data);
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function getNewReview(Request $request, Review $review)
    {
        try {
            $data = $review->with('product')
                // ->where('view', false)
                ->has('product')
                ->orderBy('updated_at', 'desc')
                ->limit(3)
                ->get();
            return NotificationTransformer::newReview($data);
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function getNewTransaction(Request $request, Transaction $transaction)
    {
        try {
            $data = $transaction->where('status_id', 1)
                ->orderBy('created_at', 'desc')
                ->get();
            return NotificationTransformer::newTransaction($data);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
