<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\V1\User;
use App\Models\V1\Role;
use App\Models\V1\Permission;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $routeCollection = Route::getRoutes();
        foreach ($routeCollection as $value) {
            $access = $value->getName();
            if ($access !== NULL) {
                $description = explode('.', $access);
                $json = [];
                foreach ($description as $index => $dc) {
                    $json['tier_' . $index] = $dc;
                }
                $json = json_encode($json);
                Permission::firstOrCreate(
                    ['access' => $access],
                    ['description' => $json]
                );
            }
        }
        $permission = Permission::all();
        $role = Role::create([
            'name' => 'Super admin',
            'description' => 'super admin'
        ]);
        $role->permission()->sync($permission->pluck('id')->toArray());
        User::create([
            'email' => 'admin@mail.com',
            'password' => Hash::make('admin'),
            'can_access_customer' => false,
            'can_access_admin' => false,
            'can_access_super_admin' => true,
            'role_id' => $role->id
        ]);
    }
}
