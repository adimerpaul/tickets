<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Moneda;

class MonedaSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['codigo'=>'EGP','nombre'=>'Libra Egipcia','simbolo'=>'£','orden'=>1,'activo'=>true],
            ['codigo'=>'EUR','nombre'=>'Euro','simbolo'=>'€','orden'=>2,'activo'=>true],
            ['codigo'=>'USD','nombre'=>'Dólar','simbolo'=>'$','orden'=>3,'activo'=>true],
            ['codigo'=>'USDT','nombre'=>'USDT','simbolo'=>'₮','orden'=>4,'activo'=>true],
        ];

        foreach ($rows as $r) {
            Moneda::updateOrCreate(['codigo'=>$r['codigo']], $r);
        }
    }
}
