<?php

namespace App\Http\Controllers\V1\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Requests\Dashboard\Role\CreateRequest;

use App\Http\Response\Dashboard\RoleTransformer;

use App\Models\V1\Role;
use DB;

class RoleController extends Controller
{
    public function getData(Request $request, $id = NULL)
    {
        try {
            $data = Role::with('permission');
            $per_page = $request->input('per_page', 10);
            $order_by = $request->input('oder_by', 'id');
            $sort = $request->input('sort', 'asc');

            if ($id !== NULL) {
                $data = $data->where('id', $id)->firstOrFail();
                return RoleTransformer::detail($data);
            } else if ($request->filled('all')) {
                $data = $data->get();
                return RoleTransformer::list($data);
            } else {
                $data = $data->orderBy($order_by, $sort)->paginate($per_page);
                $data->appends($request->all());
                return RoleTransformer::list($data);
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }


    public function create(CreateRequest $request)
    {
        DB::beginTransaction();
        try {
            $role = Role::create([
                'name' => $request->name,
                'description' => $request->description
            ]);
            $role->permission()->sync($request->permission);

            $role = Role::where('id', $role->id)->with('permission')->first();
            DB::commit();
            return RoleTransformer::detail($role);
        } catch (\Exception $e) {
            DB::rollback();
            throw new \Exception($e->getMessage());
        }
    }

    public function update(CreateRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $role = Role::where('id', $id)->firstOrFail();
            $role->update([
                'name' => $request->name,
                'description' => $request->description
            ]);
            $role->permission()->sync($request->permission);
            DB::commit();
            return RoleTransformer::detail($role->fresh());
        } catch (\Exception $e) {
            DB::rollback();
            throw new \Exception($e->getMessage());
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $role = Role::where('id', $id)->firstOrFail();
            $role->delete();
            $role->permission()->detach();
            DB::commit();
            return RoleTransformer::delete($role);
        } catch (\Exception $e) {
            DB::rollback();
            throw new \Exception($e->getMessage());
        }
    }
}
