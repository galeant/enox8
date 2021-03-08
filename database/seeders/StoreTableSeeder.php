<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Carbon\Carbon;

use App\Models\V1\Store;

class StoreTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // DB::table('store')->truncate();

        $store = [
            [
                'name' => 'Lenovo',
                'logo_path' => 'image/gallery/store',
                'address' => 'Jakarta Pusat',
                'phone' => '085709346591',
                'email' => 'contact@lenovo.co.id',
                'country_id' => '104',
                'province_id' => '31',
                'regency_id' => '3171',
                'district_id' => '3171010',
                'created_at' => Carbon::now()
            ]
        ];

        Store::insert($store);
    }
}
