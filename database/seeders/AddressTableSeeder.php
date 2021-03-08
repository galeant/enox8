<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Models\V1\Address;

class AddressTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('address')->truncate();

        $address = [
            [
                'user_id' => '2',
                'address' => 'Jl. kodiklat TNI, Gg. masjid nurul iman No 76 rt 002 rw 06 kel. buara',
                'country_id' => '104',
                'province_id' => '36',
                'regency_id' => '3674',
                'district_id' => '3674030',
                'address_name' => 'Rumah Pink',
                'recipient_name' => 'Nengsih',
                'phone' => '081122223333',
                'postal_code' => '15316',
                'latitude' => '-6.338165',
                'longitude' => '106.691768',
                'main_address' => '1',
                'created_at' => Carbon::now()
            ]
        ];

        Address::insert($address);
    }
}
