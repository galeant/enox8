<?php

namespace App\Http\Controllers\V1\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;
use Illuminate\Filesystem\Filesystem;

use App\Http\Requests\Dashboard\Blog\CreateRequest;
use App\Http\Requests\Dashboard\Blog\ChangeStatusRequest;
use App\Http\Response\Dashboard\BlogTransformer;

use App\Models\V1\Blog;
use App\Models\V1\BlogAttribute;
use DB;
use Auth;

class BlogController extends Controller
{
    private $folder_path = 'public/blog/';

    public function getData(Request $request, $id = null)
    {
        try {
            $per_page = $request->input('per_page', 10);

            $data = Blog::with('tag', 'category', 'creator');
            if ($request->filled('status')) {
                $data = $data->where('status', $request->status);
            }

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
                        default:
                            return transformer(null, 500, 'Operator filter not send', false);
                    }
                    $data  = $data->where($ft['field'], $opr, $ft['value']);
                }
            }

            $order = 'title';
            if (isset($request->sort['field'])) {
                $order = $request->sort['field'];
            }

            $sort = 'desc';
            if (isset($request->sort['value'])) {
                $sort = $request->sort['value'];
            }

            // if ($request->filled('title')) {
            //     // Pencarian berdasarkan title atau content
            //     $data = $data->where('title', 'ilike', '%' . $request->search . '%')
            //         ->orWhere('content', 'ilike', '%' . $request->search . '%');
            // }

            // if($request->filled('id')){
            //    $ids = explode(',',$request->id);
            //    $data = $data->whereIn('id',$ids);
            // }

            // if ($request->filled('category')) {
            //     $data = $data->whereHas('category', function ($q) use ($request) {
            //         $q->where('name', 'ilike', '%' . $request->category . '%')
            //             ->orWhere('id', $request->category);
            //     });
            // }

            // if ($request->filled('tag')) {
            //     $data = $data->whereHas('tag', function ($q) use ($request) {
            //         $q->where('name', 'ilike', '%' . $request->tag . '%')
            //             ->orWhere('id', $request->tag);
            //     });
            // }

            $sort = 'created_at';
            if ($request->filled('sort')) {
                switch ($request->sort) {
                    case 'title':
                        $sort = 'title';
                        break;
                    case 'created_at':
                        $sort = 'created_at';
                        break;
                }
            }

            $order = $request->input('order', 'DESC');
            if ($order !== 'DESC') {
                $order = 'ASC';
            }

            if ($id !== NULL) {
                $data = $data->where('id', $id)->firstOrFail();
                return BlogTransformer::detail($data);
            } else if ($request->filled('all')) {
                $data = $data->get();
                return BlogTransformer::list($data);
            } else {
                $data  = $data->orderBy($sort, $order)->paginate($per_page);
                $data->appends($request->all());
                return BlogTransformer::list($data);
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function create(CreateRequest $request)
    {

        DB::beginTransaction();
        $delete_path = [];
        try {
            $user = Auth::user();
            $img = imageUpload($this->folder_path, $request->banner);
            $delete_path[] = str_replace('storage', 'public', $img);

            $data = Blog::create([
                'title' => $request->title,
                'slug' => str_slug($request->title),
                'short_content' => $request->short_content,
                'content' => $request->content,
                'created_by' => $user->id,
                'banner' => $img
            ]);

            $tag = [];
            foreach ($request->tag as $tg) {
                $tExist = BlogAttribute::where(function ($q) use ($tg) {
                    $q->where('id', (int) $tg)
                        ->orWhere('name', $tg);
                })->where('type', 'tag')->first();

                if ($tExist === NULL) {
                    $tExist = BlogAttribute::create([
                        'name' => $tg,
                        'type' => 'tag'
                    ]);
                }
                $tag[] = $tExist->id;
            }
            $data->tag()->attach($tag);

            $category = [];
            foreach ($request->category as $ct) {
                $cExist = BlogAttribute::where(function ($q) use ($ct) {
                    $q->where('id', (int) $ct)
                        ->orWhere('name', $ct);
                })->where('type', 'category')->first();

                if ($cExist === NULL) {
                    $cExist = BlogAttribute::create([
                        'name' => $ct,
                        'type' => 'category'
                    ]);
                }
                $category[] = $cExist->id;
            }
            $data->category()->attach($category);

            DB::commit();
            return BlogTransformer::detail($data->fresh());
        } catch (\Exception $e) {
            DB::rollBack();
            foreach ($delete_path as $dp) {
                Storage::delete($dp);
            }
            throw new \Exception($e->getMessage());
        }
    }

    public function update(CreateRequest $request, $id)
    {
        DB::beginTransaction();
        $delete_path = [];
        try {
            $user = Auth::user();
            $data = Blog::where('id', $id)->firstOrFail();
            $fill = [
                'short_content' => $request->short_content,
                'title' => $request->title,
                'slug' => str_slug($request->title),
                'content' => $request->content,
                'created_by' => $user->id
            ];

            if ($data->banner !== str_replace(url('/') . '/', '', $request->banner)) {
                $fill['banner'] = imageUpload($this->folder_path, $request->banner);
                $delete_path[] = str_replace('storage', 'public', $fill['banner']);
                Storage::delete($data->physical_path_banner);
            }
            $data->update($fill);

            $data->tag()->detach();
            $data->category()->detach();

            $tag = [];
            foreach ($request->tag as $tg) {
                $tExist = BlogAttribute::where(function ($q) use ($tg) {
                    $q->where('id', (int) $tg)
                        ->orWhere('name', $tg);
                })->where('type', 'tag')->first();

                if ($tExist === NULL) {
                    $tExist = BlogAttribute::create([
                        'name' => $tg,
                        'type' => 'tag'
                    ]);
                }
                $tag[] = $tExist->id;
            }
            $data->tag()->attach($tag);

            $category = [];
            foreach ($request->category as $ct) {
                $cExist = BlogAttribute::where(function ($q) use ($ct) {
                    $q->where('id', (int) $ct)
                        ->orWhere('name', $ct);
                })->where('type', 'category')->first();

                if ($cExist === NULL) {
                    $cExist = BlogAttribute::create([
                        'name' => $ct,
                        'type' => 'category'
                    ]);
                }
                $category[] = $cExist->id;
            }
            $data->category()->attach($category);

            DB::commit();
            return BlogTransformer::detail($data->fresh());
        } catch (\Exception $e) {
            DB::rollBack();
            foreach ($delete_path as $dp) {
                Storage::delete($dp);
            }
            throw new \Exception($e->getMessage());
        }
    }

    public function changeStatus(ChangeStatusRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = Blog::whereIn('id', $request->id)->get();
            foreach ($data as $d) {
                $d->update([
                    'status' => $request->status
                ]);
            }

            DB::commit();
            return BlogTransformer::list($data->fresh());
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $data = Blog::where('id', $id)->firstOrFail();
            $data->tag()->detach();
            $data->category()->detach();
            $data->delete();
            DB::commit();

            return BlogTransformer::delete($data);
        } catch (\Exception $e) {
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

        Storage::makeDirectory('public/blog');
        $folder_path = storage_path('app/public/blog');
        \File::put($folder_path . '/' . $imageName, base64_decode($image));
        return 'storage/blog/' . $imageName;
    }
}
