<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('roles')->insert(
            array(
                [
                    'id' => 1,
                    'name' => 'Administrador',
                    'description' => 'Administradores de área'
                ],
                [
                    'id' => 2,
                    'name' => 'Vendedor',
                    'description' => 'Vendedor área venta'
                ],
                [
                    'id' => 3,
                    'name' => 'Almacenero',
                    'description' => 'Almacenero área compras'
                ],
                [
                    'id' => 4,
                    'name' => 'Cliente',
                    'description' => 'Cliente del sistema'
                ],
            )
        );
    }
}
