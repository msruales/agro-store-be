<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PersonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('persons')->insert([
            'id' => 1,
            'email' => 'admin@admin.com',
            'full_name' => 'Admin'
        ]);
        DB::table('persons')->insert([
            'id' => 2,
            'document_type' => "RUC",
            'document_number' => "9999999999999",
            'email' => 'consumidor@consumidor.com',
            'full_name' => 'Consumidor Final'
        ]);
    }
}
