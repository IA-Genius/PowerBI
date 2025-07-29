<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vodafone_asignaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vodafone_id')->constrained('historial_registros_vodafone')->onDelete('cascade');
            $table->foreignId('asignado_de_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('asignado_a_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // quien hizo el cambio
            $table->string('motivo')->nullable();
            $table->timestamp('fecha')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vodafone_asignaciones');
    }
};
