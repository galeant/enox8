<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Models\V1\Product;

class ProductTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // DB::table('product')->truncate();

        $product = [
            [
                'slug' => 'lenovo-idepad-310',
                'name' => 'Lenovo Idepad 310',
                'code' => 'li310',
                'main_image_url' => 'http://localhost/image/gallery',
                'description' => 'Laptop lenovo ideapad 310 with AMD A12 Processor',
                'weight' => '1000',
                'condition' => '1',
                'ranking' => '4',
                'meta_title' => 'lenovo ideapad 310',
                'meta_description' => 'laptop lenovo ideapad 310',
                'ordering_price' => '70000',
                'store_id' => '1',
                // 'price' => NULL,
                // 'stock' => NULL,
                // 'promo_name' => NULL,
                // 'promo_value' => NULL,
                // 'promo_unit' => NULL,
                'code' => '12345',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'slug' => 'lenovo-idepad-c340',
                'name' => 'Lenovo Idepad C340',
                'code' => 'li340',
                'main_image_url' => 'http://localhost/image/gallery',
                'description' => 'Laptop lenovo ideapad C340 with Intel Core i5 Processor',
                'weight' => '1000',
                'condition' => '1',
                'ranking' => '4',
                'meta_title' => 'lenovo ideapad C340',
                'meta_description' => 'laptop lenovo ideapad C340',
                'ordering_price' => '80000',
                'store_id' => '3',
                // 'price' => '90000',
                // 'stock' => '12',
                // 'promo_name' => 'Promo Laptop Lenovo Idepad C340 Core i5',
                // 'promo_value' => '10000',
                // 'promo_unit' => '10',
                'code' => '54321',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        ];

        // DB::table('product')->insert($product);

        foreach ($product as $data) {
            Product::updateOrCreate(['slug' => $data['slug']], $data);
        }
    }
}
