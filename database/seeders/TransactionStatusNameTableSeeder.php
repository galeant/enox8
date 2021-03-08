<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\V1\TransactionStatus;

class TransactionStatusNameTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $status = [
            [
                'name' => 'Waiting Payment'
            ],
            [
                'name' => 'Cancel'
            ],
            [
                'name' => 'Payment Expaired'
            ],
            [
                'name' => 'Payment Accept'
            ],
            [
                'name' => 'On Check Payment'
            ],
            [
                'name' => 'On Packing'
            ],
            [
                'name' => 'On Delivery'
            ],
            [
                'name' => 'Complete'
            ],
            [
                'name' => 'Complained'
            ],
            [
                'name' => 'Decline'
            ],
            [
                'name' => 'Compensation'
            ]
        ];

        foreach ($status as $data) {
            TransactionStatus::create($data);
        }
    }
}
