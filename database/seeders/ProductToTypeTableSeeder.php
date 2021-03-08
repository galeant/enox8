<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ProductToTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('product_to_type')->truncate();

        $product_to_type = [
            [
                'product_id' => '1',
                'type_id' => '4',
                'stock' => '20',
                'price' => '10000',
                'promo_name' => 'Promo Laptop Lenovo Ideapad 310',
                'promo_value' => '1000',
                'promo_unit' => '2'
            ],
            [
                'product_id' => '2',
                'type_id' => '2',
                'stock' => '20',
                'price' => '10000',
                'promo_name' => 'Promo Laptop Lenovo Idepad C340',
                'promo_value' => '1000',
                'promo_unit' => '2'
            ]
        ];

        DB::table('product_to_type')->insert($product_to_type);
    }
}
