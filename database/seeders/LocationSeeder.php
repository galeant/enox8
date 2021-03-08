<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use DB;

class LocationSeeder extends Seeder
{
    protected static $path = __DIR__ . '/File';
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('countries')->truncate();
        $country = base_path('database/seeders/File/countries.sql');
        DB::unprepared(file_get_contents($country));

        DB::table('provinces')->truncate();
        $province = base_path('database/seeders/File/provinces.sql');
        DB::unprepared(file_get_contents($province));

        DB::table('regencies')->truncate();
        $regency = base_path('database/seeders/File/regencies.sql');
        DB::unprepared(file_get_contents($regency));

        DB::table('districts')->truncate();
        $district = base_path('database/seeders/File/districts.sql');
        DB::unprepared(file_get_contents($district));

        DB::table('villages')->truncate();
        $village = base_path('database/seeders/File/villages.sql');
        DB::unprepared(file_get_contents($village));


        // $options['delimiter'] = '|';
        // $country = parse_csv(self::$path . '/countries.csv', $options);
        // $province = parse_csv(self::$path . '/provinces.csv', $options);
        // $regency = parse_csv(self::$path . '/regencies.csv', $options);
        // $district = parse_csv(self::$path . '/districts.csv', $options);
        // $village = parse_csv(self::$path . '/villages.csv', $options);

        // $country = collect($country)->chunk(5000);
        // $province = collect($province)->transform(function ($v) {
        //     return [
        //         'id' => $v['id'],
        //         'country_id' => $v['country_id'],
        //         'name' => ucwords(strtolower($v['name'])),
        //         'created_at' => $v['created_at'],
        //         'updated_at' => $v['updated_at']
        //     ];
        // })->chunk(5000);

        // $regency = collect($regency)->transform(function ($v) {
        //     $name = explode(' ', $v['name']);
        //     $type = ucwords(strtolower($name[0]));
        //     unset($name[0]);
        //     $r_name = ucwords(strtolower(implode(' ', $name)));
        //     return [
        //         'id' => $v['id'],
        //         'province_id' => $v['province_id'],
        //         'name' => $r_name,
        //         'type' => $type,
        //         'created_at' => $v['created_at'],
        //         'updated_at' => $v['updated_at']
        //     ];
        // })->chunk(5000);
        // $district = collect($district)->transform(function ($v) {
        //     return [
        //         'id' => $v['id'],
        //         'regency_id' => $v['regency_id'],
        //         'name' => $v['name'],
        //         'created_at' => $v['created_at'],
        //         'updated_at' => $v['updated_at']
        //     ];
        // })->chunk(5000);
        // $village = collect($village)->transform(function ($v) {
        //     return [
        //         'id' => $v['id'],
        //         'district_id' => $v['district_id'],
        //         'name' => $v['name'],
        //         'created_at' => $v['created_at'],
        //         'updated_at' => $v['updated_at']
        //     ];
        // })->chunk(5000);

        // foreach ($country as $ct) {
        //     DB::table('countries')->insertOrIgnore($ct->toArray());
        // }
        // foreach ($province as $pr) {
        //     DB::table('provinces')->insertOrIgnore($pr->toArray());
        // }

        // foreach ($regency as $rg) {
        //     DB::table('regencies')->insertOrIgnore($rg->toArray());
        // }

        // foreach ($district as $dt) {
        //     DB::table('districts')->insertOrIgnore($dt->toArray());
        // }
        // foreach ($village as $vl) {
        //     DB::table('villages')->insertOrIgnore($vl->toArray());
        // }
    }
}
