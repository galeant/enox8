<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ShoppingCartTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('shopping_cart')->truncate();

        $shopping_cart = [
            [
                'user_id' => '2',
                'product_id' => '2',
                'type_id' => '1',
                'price' => '80000',
                'total_price' => '90000',
                'qty' => '1',
                'discount_value' => NULL,
                'note' => 'Laptop Lenovo C340 warna hitam',
                'created_at' => Carbon::now()
            ]
        ];

        DB::table('shopping_cart')->insert($shopping_cart);
    }
}
