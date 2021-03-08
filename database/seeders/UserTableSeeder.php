<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Carbon\Carbon;

use App\Models\V1\User;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->truncate();

        $users = [
            [
                'first_name' => 'mahmuddin nurul',
                'last_name' => 'fajri',
                'email' => 'mahmuddinnf@gmail.com',
                'password' => bcrypt(base64_decode('YWRtaW4xMjM0NQ==')),
                'phone' => '085709346592',
                'subscribe' => true,
                'email_verified_at' => Carbon::now(),
                'created_at' => Carbon::now()
            ],
            [
                'first_name' => 'mahmuddin nurul',
                'last_name' => 'fajri',
                'email' => 'mahmuddin.fajri@noxus.co.id',
                'password' => bcrypt(base64_decode('YWRtaW4xMjM0NQ==')),
                'phone' => '085709346591',
                'subscribe' => true,
                'email_verified_at' => Carbon::now(),
                'created_at' => Carbon::now()
            ],
            [
                'first_name' => 'admin',
                'last_name' => 'fajri',
                'email' => 'mahmuddin.fajri@noxus.co.id',
                'password' => bcrypt(base64_decode('YWRtaW4xMjM0NQ==')),
                'phone' => '085709346591',
                'subscribe' => true,
                'email_verified_at' => Carbon::now(),
                'created_at' => Carbon::now()
            ]
        ];

        User::insert($users);
    }
}
