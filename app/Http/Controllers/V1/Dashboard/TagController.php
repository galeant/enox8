<?php

namespace App\Http\Controllers\V1\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Response\Dashboard\TagTransformer;
use App\Http\Requests\Dashboard\Tag\CreateRequest;
use App\Models\V1\Tag;
use DB;

class TagController extends Controller
{
    public function getData(Request $request, $id = null)
    {
        try {
            $per_page = $request->input('per_page', 10);
            $data = new Tag;

            $order = $request->input('order', 'name');
            $sort = $request->input('sort', 'desc');

            // if($request->filled('name')){
            //     $data = $data->where('name','ilike','%'.$request->name.'%');
            // }

            // if($request->filled('id')){
            //     $ids = explode(',',$request->id);
            //     $data = $data->whereIn('id',$ids);
            // }
            if ($id != null) {
                $data = $data->where('id', $id)->first();
                return TagTransformer::detail($data);
            } else if ($request->filled('all')) {
                $data = $data->get();
                return TagTransformer::list($data);
            } else {
                $data = $data->orderBy($order, $sort)->paginate($per_page);
                $data->appends($request->all());
                return TagTransformer::list($data);
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }


    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $data = Tag::where('id', $id)->firstOrFail();
            $data->delete();
            DB::commit();
            return TagTransformer::delete($data);
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }
}
