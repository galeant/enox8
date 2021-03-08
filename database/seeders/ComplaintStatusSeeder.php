<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\V1\ComplaintStatus;

class ComplaintStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $complaint = [
            [
                'name' => 'Waiting Action'
            ],
            [
                'name' => 'Decline'
            ],
            [
                'name' => 'Accept'
            ],
            [
                'name' => 'Cash Return Process'
            ],
            [
                'name' => 'Cash Return Complete'
            ],
            [
                'name' => 'Product Return To Store'
            ],
            [
                'name' => 'Product Return Process'
            ],
            [
                'name' => 'Product Return Complete'
            ],
            [
                'name' => 'Closed'
            ]
        ];

        foreach ($complaint as $c) {
            ComplaintStatus::create($c);
        }
    }
}
