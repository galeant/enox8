<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\V1\Admin;
use App\Models\V1\Store;


class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $store = Store::create([
            'name' => 'Toko Saya',
            'address' => 'Alamat Toko Saya',
            'phone' => 112233445566778899,
            'email' => 'tokosaya@mail.com',
            'country_id' => '104',
            'province_id' => '36',
            'regency_id' => '3601',
            'district_id' => '3601010',
        ]);
        Admin::create([
            'store_id' => $store->id,
            'username' => 'admin',
            'email' => 'admin@mail.com',
            'password' => Hash::make('admin'),
            'phone' => '0001112223344',
            'status' => 1
        ]);

        $store = Store::create([
            'name' => 'Zayba Store',
            'address' => 'Alamat Toko Saya',
            'phone' => '0856833636369',
            'email' => 'tokosaya@mail.com',
            'country_id' => '104',
            'province_id' => '32',
            'regency_id' => '3201',
            'district_id' => '3201180',
        ]);
        Admin::create([
            'store_id' => $store->id,
            'username' => 'mahmuddin',
            'email' => 'mahmuddinnf@gmail.com',
            'password' => Hash::make('admin12345'),
            'phone' => '085666588885',
            'status' => 1
        ]);
    }
}
