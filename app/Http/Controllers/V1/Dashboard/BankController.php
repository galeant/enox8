<?php

namespace App\Http\Controllers\V1\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;

use App\Http\Requests\Dashboard\Bank\CreateRequest;
use App\Http\Response\Dashboard\BankTransformer;

use App\Models\V1\Bank;
use Carbon\Carbon;
use DB;

class BankController extends Controller
{
    public function index(Request $request, $id = NULL)
    {
        try {
            $per_page = $request->input('per_page', 10);
            $data = new Bank;

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
                return BankTransformer::detail($data);
            } else if ($request->filled('all')) {
                $data = $data->get();
                return BankTransformer::list($data);
            } else {
                $data  = $data->orderBy($sort, $order)->paginate($per_page);
                $data->appends($request->all());
                return BankTransformer::list($data);
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function create(CreateRequest $request)
    {
        DB::beginTransaction();
        try {
            $image = $request->image;
            if (strpos($request->image, 'http://') === false && strpos($request->image, 'https://') === false) {
                $image = $this->imageConverter($request->icon);
            }
            $data = Bank::create([
                'name' => $request->name,
                'account_number' => $request->account_number,
                'type' => $request->type,
                'image' => $image
            ]);

            DB::commit();
            return BankTransformer::detail($data);
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }


    public function update(CreateRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $data = Bank::where('id', $id)->firstOrFail();
            $image = $request->image;
            if (strpos($request->image, 'http://') === false && strpos($request->image, 'https://') === false) {
                $image = $this->imageConverter($request->icon);
            }
            if (strpos($request->image, url('/')) !== false) {
                $image = str_replace(url('/'), '', $request->image);
            }
            $data->update([
                'name' => $request->name,
                'account_number' => $request->account_number,
                'type' => $request->type,
                'image' => $image
            ]);

            if ($request->image != asset($data->image)) {
                Storage::delete($data->image_physical_path);
            }
            DB::commit();
            return BankTransformer::detail($data->fresh());
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $data = Bank::where('id', $id)->firstOrFail();
            $data->delete();
            DB::commit();
            return BankTransformer::delete($data);
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }

    private function imageConverter($image)
    {
        $img = preg_replace('/^data:image\/\w+;base64,/', '', $image);
        $type = explode(';', $image)[0];
        $type = explode('/', $type)[1]; // png or jpg etc

        $image = str_replace('data:image/' . $type . ';base64,', '', $image);
        $image = str_replace(' ', '+', $image);
        $imageName = Str::uuid() . '.' . $type;

        Storage::makeDirectory('public/icon');
        $folder_path = storage_path('app/public/icon');
        \File::put($folder_path . '/' . $imageName, base64_decode($image));
        return asset('storage/icon/' . $imageName);
    }
}
