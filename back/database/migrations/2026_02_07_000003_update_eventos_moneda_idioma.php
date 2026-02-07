<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            if (!Schema::hasColumn('eventos', 'moneda_id')) {
                $table->foreignId('moneda_id')->nullable()->constrained('monedas')->nullOnDelete();
            }
            if (!Schema::hasColumn('eventos', 'idioma_id')) {
                $table->foreignId('idioma_id')->nullable()->constrained('idiomas')->nullOnDelete();
            }
        });

        // Migrar datos desde columna moneda (codigo) si existe
        if (Schema::hasColumn('eventos', 'moneda')) {
            $map = DB::table('monedas')->pluck('id', 'codigo')->all();
            $eventos = DB::table('eventos')->select('id', 'moneda')->get();
            foreach ($eventos as $ev) {
                if ($ev->moneda && isset($map[$ev->moneda])) {
                    DB::table('eventos')
                        ->where('id', $ev->id)
                        ->update(['moneda_id' => $map[$ev->moneda]]);
                }
            }
        }

        // Asignar idioma por defecto si existe
        $defaultIdiomaId = DB::table('idiomas')->orderBy('orden')->orderBy('id')->value('id');
        if ($defaultIdiomaId) {
            DB::table('eventos')
                ->whereNull('idioma_id')
                ->update(['idioma_id' => $defaultIdiomaId]);
        }

        // Eliminar columna antigua moneda si existe
        if (Schema::hasColumn('eventos', 'moneda')) {
            Schema::table('eventos', function (Blueprint $table) {
                $table->dropColumn('moneda');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('eventos', 'moneda')) {
            Schema::table('eventos', function (Blueprint $table) {
                $table->string('moneda', 10)->default('EGP');
            });
        }

        Schema::table('eventos', function (Blueprint $table) {
            if (Schema::hasColumn('eventos', 'moneda_id')) {
                $table->dropConstrainedForeignId('moneda_id');
            }
            if (Schema::hasColumn('eventos', 'idioma_id')) {
                $table->dropConstrainedForeignId('idioma_id');
            }
        });
    }
};
