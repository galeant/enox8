<?php

namespace App\Http\Controllers\V1\Client;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Requests\Client\Comment\PostComment;
use App\Http\Requests\Client\Comment\EditComment;
use App\Http\Requests\Client\Comment\DeleteComment;

use App\Http\Response\Client\CommentTransformer;

use App\Models\V1\Comment;
use App\Models\V1\Product;
use DB;

class CommentController extends Controller
{
    public function postComment(PostComment $request)
    {
        DB::beginTransaction();
        try {
            $request->all();
            $user = auth()->user();
            $product = Product::where('slug', $request->product_slug)->firstOrFail();

            $comment = Comment::create([
                'parent_id' => $request->filled('comment_id') ? $request->comment_id : 0,
                'user_id' => $user->id,
                'product_id' => $product->id,
                'content' => $request->comment
            ]);

            if ($request->filled('comment_id')) {
                $parent = Comment::with('user')->where('id', $request->comment_id)->firstOrFail();
                sendNotification($parent->user->fcm_token, [
                    'title' => 'Your comment has been reply',
                    'body' => $request->comment
                ]);
            }

            $admin_fcm_token = [];
            foreach ($product->store->user as $us) {
                $admin_fcm_token[] = $us->fcm_token;
            }
            sendNotification(collect($admin_fcm_token)->flatten()->toArray(), [
                'title' => 'Your product has been commented',
                'body' => $request->comment
            ]);
            DB::commit();
            $data = Comment::withTrashed()
                ->where('product_id', $product->id)
                ->where('parent_id', 0)
                ->with('children')
                ->orderBy('created_at', 'DESC')
                ->paginate(10);

            return CommentTransformer::getList($data);
        } catch (\Exception $e) {
            DB::rollback();
            throw new \Exception($e->getMessage());
        }
    }

    public function editComment(EditComment $request, $id)
    {
        DB::beginTransaction();
        try {
            $comment = Comment::where('id', $id)->firstOrFail();
            $comment->update([
                'content' => $request->comment
            ]);
            DB::commit();
            $data = Comment::withTrashed()
                ->where('product_id', $comment->product_id)
                ->where('parent_id', 0)
                ->with('children')
                ->orderBy('created_at', 'DESC')
                ->paginate(10);

            return CommentTransformer::getList($data);
        } catch (\Exception $e) {
            DB::rollback();
            throw new \Exception($e->getMessage());
        }
    }

    public function deleteComment(DeleteComment $request, $id)
    {
        DB::beginTransaction();
        try {
            $comment = Comment::where('id', $id)->firstOrFail();
            $comment->delete();
            DB::commit();
            $data = Comment::withTrashed()
                ->where('product_id', $comment->product_id)
                ->where('parent_id', 0)
                ->with('children')
                ->orderBy('created_at', 'DESC')
                ->paginate(10);

            return CommentTransformer::getList($data);
        } catch (\Exception $e) {
            DB::rollback();
            throw new \Exception($e->getMessage());
        }
    }
    public function getComment(Request $request)
    {
        try {
            $data = Comment::with('children')->withTrashed()->where('parent_id', 0)->get();
            return CommentTransformer::getList($data);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
