<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Carbon\Carbon;

use App\Models\V1\Type;

class TypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // DB::table('type')->truncate();

        $type = [
            [
                'name' => 'Singel Price',
                'description' => 'singel_price',
                'slug' => str_slug('single_price'),
                'parent_id' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'name' => 'Lenovo C340',
                'description' => 'Laptop lenovo type C340',
                'slug' => 'lenovo-c340',
                'parent_id' => '0',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'name' => 'Lenovo C340 Core i5',
                'description' => 'Laptop from Lenovo Brand wity type C340 SKU Intel Core i5',
                'slug' => 'lenovo-c340-core-i5',
                'parent_id' => '1',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'name' => 'Lenovo C340 Core i7',
                'description' => 'Laptop from Lenovo Brand wity type C340 SKU Intel Core i7',
                'slug' => 'lenovo-c340-core-i7',
                'parent_id' => '1',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'name' => 'Lenovo 310 A12',
                'description' => 'Lenovo 310 A12',
                'slug' => 'lenovo-310-a12',
                'parent_id' => '0',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        ];

        Type::insert($type);
    }
}
