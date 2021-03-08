<?php

namespace App\Http\Controllers\V1\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


use App\Http\Requests\Dashboard\Comment\ReplyRequest;

use App\Http\Response\Dashboard\CommentTransformer;

use App\Models\V1\Comment;
use App\Models\V1\Product;
use DB;



class CommentController extends Controller
{
    public function notification(Request $request)
    {
        try {
            $data = Comment::where('view', false)->limit(5)->orderBy('created_at', 'desc')->get();
            return CommentTransformer::list($data);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function getProductCommentList(Request $request, $id = NULL)
    {
        try {
            if ($id !== NULL) {
                $data = Comment::where('parent_id', 0)->whereHas('product', function ($q) use ($id) {
                    $q->where('id', $id);
                })->paginate(10);
                return CommentTransformer::list($data);
            } else {
                $data = Product::whereHas('comment')->get();
                return CommentTransformer::product($data);
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function getComment(Request $request, $id = NULL)
    {
        try {
            $per_page = $request->input('per_page', 10);
            $data = new Comment;

            if ($id !== NULL) {
                $data = $data->with('children')->where('id', $id)->firstOrFail();
                $children = Comment::where('parent_id', $id)->paginate(10);
                return CommentTransformer::detail($data, $children);
            } else if ($request->filled('all')) {
                $data = $data->paginate(10);
                return CommentTransformer::list($data);
            } else {
                $data = $data->where('parent_id', 0)
                    ->orderBy('created_at', 'desc')
                    ->paginate($request->per_page);
                $data->appends($request->all());
                return CommentTransformer::list($data);
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function reply(ReplyRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $user = auth()->user();
            $data = Comment::where('id', $id)->firstOrFail();
            $data->update([
                'view' => true
            ]);

            $reply = Comment::create([
                'user_id' => $user->id,
                'parent_id' => $id,
                'content' => $request->comment,
                'product_id' => $data->product_id
            ]);

            $comment = Comment::where('product_id', $data->product_id)->paginate(10);
            DB::commit();
            return CommentTransformer::list($comment);
        } catch (\Exception $e) {
            DB::rollback();
            throw new \Exception($e->getMessage());
        }
    }

    public function delete($id)
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

            return CommentTransformer::list($data);
        } catch (\Exception $e) {
            DB::rollback();
            throw new \Exception($e->getMessage());
        }
    }

    private static function getParentId($comment)
    {
        $parentes = false;
        $parent_id = NULL;
        while ($parentes == false) {
            if ($comment->parent !== NULL) {
                $parent_id = $comment->parent->id;
                $parentes = false;
            } else {
                $parentes = true;
            }
            $comment = $comment->parent;
        }
        return implode(' > ', array_reverse($result));
    }
}
