<?php

namespace App\Http\Controllers\V1\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\V1\BlogAttribute;
use DB;
use Auth;
use Route;

use App\Http\Requests\Dashboard\BlogAttribute\CreateRequest;
use App\Http\Response\Dashboard\BlogAttributeTransformer;

class BlogAttributeController extends Controller
{

    public function __construct()
    {
        // if(Route::input('attribute') !== 'category' && Route::input('attribute') !== 'tag'){
        //     throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
        // }
    }
    public function index(Request $request, $type, $id = NULL)
    {
        $per_page = $request->input('per_page', 10);
        // $sort = $request->input('sort','id');
        try {
            $order = $request->input('order', 'ASC');
            $data = BlogAttribute::where('type', $type);

            if ($request->filled('filter')) {
                foreach ($request->filter as $ft) {
                    switch ($ft['operator']) {
                        case 'eq':
                            $opr = '=';
                            break;
                        case 'ne':
                            $opr = '!=';
                            break;
                        case 'lt':
                            $opr = '<';
                            break;
                        case 'le':
                            $opr = '<=';
                            break;
                        case 'gt':
                            $opr = '>';
                            break;
                        case 'ge':
                            $opr = '>=';
                            break;
                    }
                    $data  = $data->where($ft['field'], $opr, $ft['value']);
                }
            }

            $order = 'name';
            if (isset($request->sort['field'])) {
                $order = $request->sort['field'];
            }

            $sort = 'desc';
            if (isset($request->sort['value'])) {
                $sort = $request->sort['value'];
            }

            // if($request->filled('name')){
            //     $data = $data->where('name','ilike','%'.$request->name.'%');
            // }

            // if($request->filled('id')){
            //     $ids = explode(',',$request->id);
            //     $data = $data->whereIn('id',$ids);

            // }
            if ($id !== NULL) {
                $data = $data->where('id', $id)->firstOrFail();
                return BlogAttributeTransformer::detail($data);
            } else if ($request->filled('all')) {
                $data = $data->get();
                return BlogAttributeTransformer::list($data);
            } else {
                $data = $data->orderBy($order, $sort)->paginate($per_page);
                $data->appends($request->all());
                return BlogAttributeTransformer::list($data);
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function create(CreateRequest $request, $type, $id = NULL)
    {
        DB::beginTransaction();
        try {
            if ($id !== NULL) {
                $data = BlogAttribute::where('id', $id)->firstOrFail();
                $data->update([
                    'name' => $request->name
                ]);
                $data = $data->fresh();
            } else {
                $data = BlogAttribute::firstOrCreate([
                    'type' => $type,
                    'name' => $request->name
                ]);
            }

            DB::commit();
            return BlogAttributeTransformer::detail($data);
        } catch (\Exception $e) {
            DB::rollback();
            throw new \Exception($e->getMessage());
        }
    }

    public function delete($type, $id)
    {
        DB::beginTransaction();
        try {
            $data = BlogAttribute::where([
                'type' => $type,
                'id' => $id
            ])->firstOrFail();
            $data->delete();
            DB::commit();
            return BlogAttributeTransformer::delete($data);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
