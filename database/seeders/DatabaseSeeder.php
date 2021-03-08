<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);
        $this->call(LocationSeeder::class);
        $this->call(OauthClientTableSeeder::class);
        $this->call(TransactionStatusNameTableSeeder::class);
        $this->call(BankTableSeeder::class);
        $this->call(ComplaintStatusSeeder::class);
        $this->call(SuperAdminSeeder::class);
    }
}
