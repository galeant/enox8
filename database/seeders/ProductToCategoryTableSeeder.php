<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ProductToCategoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('product_to_category')->truncate();

        $product_to_category = [
            [
                'product_id' => '1',
                'category_id' => '1'
            ],
            [
                'product_id' => '1',
                'category_id' => '2'
            ],
            [
                'product_id' => '2',
                'category_id' => '1'
            ],
            [
                'product_id' => '2',
                'category_id' => '2'
            ]
        ];

        DB::table('product_to_category')->insert($product_to_category);
    }
}
