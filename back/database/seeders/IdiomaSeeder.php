<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Idioma;

class IdiomaSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['codigo'=>'ES','nombre'=>'Espanol','orden'=>1,'activo'=>true],
            ['codigo'=>'EN','nombre'=>'Ingles','orden'=>2,'activo'=>true],
            ['codigo'=>'FR','nombre'=>'Frances','orden'=>3,'activo'=>true],
            ['codigo'=>'PT','nombre'=>'Portugues','orden'=>4,'activo'=>true],
        ];

        foreach ($rows as $r) {
            Idioma::updateOrCreate(['codigo' => $r['codigo']], $r);
        }
    }
}
