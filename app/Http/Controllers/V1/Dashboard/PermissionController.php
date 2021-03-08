<?php

namespace App\Http\Controllers\V1\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\V1\Permission;
use Route;

use App\Http\Response\Dashboard\PermissionTransformer;

class PermissionController extends Controller
{
    public function getData(){
        $data = Permission::all();
        return PermissionTransformer::list($data);
    }

    public function create(){
        $routeCollection = Route::getRoutes();
        foreach ($routeCollection as $value) {
            $access = $value->getName();
            if($access !== NULL){
                $description = explode('.',$access);
                $json = [];
                foreach($description as $index => $dc){
                    $json['tier_'.$index] = $dc;
                }
                $json = json_encode($json);
                Permission::firstOrCreate(
                    ['access' => $access],
                    ['description' => $json]
                );
            }
        }
        $permission = Permission::all();
        return PermissionTransformer::list($permission);
    }
}
