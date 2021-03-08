<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Models\V1\Category;

class CategoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // DB::table('category')->truncate();

        $category = [
            [
                'name' => 'Laptop',
                'description' => 'Laptop Category',
                'slug' => 'laptop',
                'parent_id' => '0',
                'created_at' => Carbon::now()
            ],
            [
                'name' => 'Laptop Lenovo',
                'description' => 'Laptop from Lenovo Brand',
                'slug' => 'laptop-lenovo',
                'parent_id' => '1',
                'created_at' => Carbon::now()
            ]
        ];

        // DB::table('category')->insert($category);

        foreach ($category as $data) {
            Category::updateOrCreate(['slug' => str_slug($data['name'])], $data);
        }
    }
}
