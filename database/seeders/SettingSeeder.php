<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('settings')->insert(
            array(
                [
                    'id' => 1,
                    'key' => 'razonSocial',
                ],
                [
                    'id' => 2,
                    'key' => 'nombreComercial',
                ],
                [
                    'id' => 3,
                    'key' => 'ruc',
                ],
                [
                    'id' => 4,
                    'key' => 'codEstablecimiento',
                ],
                [
                    'id' => 5,
                    'key' => 'codPtoEmision',
                ],
                [
                    'id' => 6,
                    'key' => 'dirMatriz',
                ],
                [
                    'id' => 7,
                    'key' => 'dirEstablecimiento',
                ],
                [
                    'id' => 8,
                    'key' => 'obligadoContabilidad',
                ],
                [
                    'id' => 9,
                    'key' => 'passwordFirma',
                ],
            )
        );
    }
}
