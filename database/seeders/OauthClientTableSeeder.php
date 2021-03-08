<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use DB;

class OauthClientTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('oauth_clients')->truncate();

        $oauth_clients = [
            [
                'name' => 'Noxus Ecommerce Personal Access Client',
                'user_id' => NULL,
                'secret' => 'jfRrwcgSlaqXoz6OFAt3DFhK2CMtAGgdogXOuDIs',
                'redirect' => 'http://localhost',
                'personal_access_client' => true,
                'password_client' => false,
                'revoked' => false,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'name' => 'Noxus Ecommerce Password Grant Client',
                'user_id' => NULL,
                'secret' => 'a8JRBb88BexXSFzIcTDThaDkHc15FdUXBTIKbeGg',
                'redirect' => 'http://localhost',
                'personal_access_client' => false,
                'password_client' => true,
                'revoked' => false,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'name' => 'Mobile App',
                'user_id' => NULL,
                'secret' => 'LKOI46489nRJWOgBXdxtPnWHjbQhRuw5y0BHZ7BK',
                'redirect' => 'http://localhost',
                'personal_access_client' => false,
                'password_client' => true,
                'revoked' => false,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        ];

        DB::table('oauth_clients')->insert($oauth_clients);
    }
}
