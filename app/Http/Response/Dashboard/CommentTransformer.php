<?php

namespace App\Http\Response\Dashboard;
use Carbon\Carbon;

class CommentTransformer{

    public static function list($response){
        if ($response instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            self::reformer($response->getCollection());
            $data = [
                'data' => $response->items(),
                'current_page' => $response->currentPage(),
                'next_page_url' => $response->nextPageUrl(),
                'prev_page_url' => $response->previousPageUrl(),
                'total' => $response->total(),
                'total_page' => $response->lastPage()
            ];
        }else{
            self::reformer($response);
            $data = [
                'data' => $response
            ];
        }
        return response()->json([
            'code' => 200,
            'message' => 'Get list success',
            'result' => $data
        ],200);
    }

    public static function detail($response,$children){
        $return = [
            'id' => $response->id,
            'username' => $response->username,
            'comment' => $response->content,
            'created_at' => Carbon::parse($response->created_at)->diffForHumans(),
            'reply' => []
        ];

        if ($children instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            $children->getCollection()->transform(function($value){
                $return = [
                    'username' => $value->username,
                'comment' => $value->content,
                'created_at' => Carbon::parse($value->created_at)->diffForHumans(),
                ];
                return $return;
            });
            $return['reply'] = [
                'data' => $children->items(),
                'current_page' => $children->currentPage(),
                'next_page_url' => $children->nextPageUrl(),
                'prev_page_url' => $children->previousPageUrl(),
                'total' => $children->total(),
                'total_page' => $children->lastPage()
            ];
        }else{
            $children->transform(function($value){
                $return = [
                    'username' => $value->username,
                    'comment' => $value->content,
                    'created_at' => Carbon::parse($value->created_at)->diffForHumans(),
                ];
                return $return;
            });
            $return['reply'] = [
                'data' => $children
            ];
        }
        return response()->json([
            'code' => 200,
            'message' => 'Get detail success',
            'result' => $return
        ],200);
    }

    public static function delete($response){
        return response()->json([
            'code' => 200,
            'message' => 'Delete success',
            'result' => $response
        ],200);
    }

    public static function reformer($response){
        $response->transform(function($value){
            $return = [
                'id' => $value->id,
                'content' => $value->content,
                'username' => $value->username,
                'created_at' => Carbon::parse($value->created_at)->diffForHumans()
            ];
            
            return $return;
        });
    }

    public static function product($response){
        if ($response instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            self::productComment($response->getCollection());
            $data = [
                'data' => $response->items(),
                'current_page' => $response->currentPage(),
                'next_page_url' => $response->nextPageUrl(),
                'prev_page_url' => $response->previousPageUrl(),
                'total' => $response->total()
            ];
        }else{
            self::productComment($response);
            $data = [
                'data' => $response
            ];
        }
        return response()->json([
            'code' => 200,
            'message' => 'Get list success',
            'result' => $data
        ],200);
    }

    private static function productComment($response){
        $response->transform(function($value){
            $return = [
                'id' => $value->id,
                'name' => $value->name,
            ];
            foreach($value->category as $category){
                $return['category'][] = [
                    'id' => $category->id,
                    'name' => $category->name
                ];
            }
            return $return;
        });
    }

    private static function flattencomment($comment){
        $return = [
            'reply_id' => $comment->id,
            // 'edit_id' => NULL,
            'comment' => []
        ];
        $return['comment'][] = [
            'comment' => $comment->content,
            'username' => $comment->username,
            'created_at' => Carbon::parse($comment->created_at)->format('d-m-Y'),
            'tier' => ($comment->user->can_access_admin && $comment->user->can_access_super_admin)?'Admin':'Customer'
        ];
        // dd($return);
        $c = $comment->children->toArray();
        // dd($c);
        $i = 1;
        $valid = [];
        array_walk_recursive($c,function($value,$key)use(&$return,&$i,&$a,&$valid){
            switch($key){
                case 'id':
                    $return['reply_id'] = $value;
                    break;
                case 'content':
                    $return['comment'][$i]['content'] = $value;
                    break;
                case 'created_at':
                    $return['comment'][$i]['created_at'] = Carbon::parse($value)->format('d-m-Y');
                    break;
                case 'username':
                    $return['comment'][$i]['username'] = $value;
                    break;
                case 'super_admin':
                    $valid[] = $value;
                    break;
                case 'admin':
                    $valid[] = $value;
                    break;
                case 'customer':
                    if(in_array(true, $valid)){
                        // $return['edit_id'] = $return['reply_id'];
                        $return['comment'][$i]['tier'] = 'Admin';
                    }else{
                        // $return['edit_id'] = NULL;
                        $return['comment'][$i]['tier'] = 'Customer';
                    }
                    $valid = [];
                    $i++;
                    break;
            }
        });
        return $return;
    }
}