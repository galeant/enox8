<?php

namespace App\Http\Controllers\V1\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Requests\Dashboard\Courier\CreateRequest;
use App\Http\Response\Dashboard\CourierTransformer;

use App\Models\V1\Courier;
use Carbon\Carbon;
use DB;

class CourierController extends Controller
{
    public function index(Request $request, $id = NULL)
    {
        DB::beginTransaction();
        try {
            $per_page = $request->input('per_page', 10);
            $data = new Courier;

            if ($request->filled('search')) {
                $data = $data->where('name', 'ilike', '%' . $request->search . '%');
            }

            $sort = 'created_at';
            if ($request->filled('sort')) {
                $sort = 'name';
            }

            $order = 'DESC';
            if ($request->filled('order')) {
                switch ($request->order) {
                    case 'high':
                        $order = 'DESC';
                        break;
                    case 'low':
                        $order = 'ASC';
                        break;
                }
            }
            if ($id !== NULL) {
                $data = $data->where('id', $id)->first();
                return CourierTransformer::detail($data);
            } else if ($request->filled('all')) {
                $data = $data->get();
                return CommentTransformer::list($data);
            } else {
                $data = $data->orderBy($sort, $order)->paginate($per_page);
                $data->appends($request->all());
                return CourierTransformer::list($data);
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
    public function create(CreateRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = Courier::create([
                'name' => $request->name,
                'type' => $request->type
            ]);

            DB::commit();
            return CourierTransformer::detail($data);
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }

    public function update(CreateRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $data = Courier::where('id', $id)->firstOrFail();
            $data->update([
                'name' => $request->name,
                'type' => $request->type
            ]);

            DB::commit();
            return CourierTransformer::detail($data->fresh());
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $data = Courier::where('id', $id)->firstOrFail();
            $data->delete();
            DB::commit();
            return CourierTransformer::delete($data);
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }
}
