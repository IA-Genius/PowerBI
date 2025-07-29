<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vodafone_auditorias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vodafone_id')->constrained('historial_registros_vodafone')->onDelete('cascade');
            $table->foreignId('asignacion_id')->nullable()->constrained('vodafone_asignaciones')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('accion');
            $table->json('campos_editados')->nullable();
            $table->timestamp('fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vodafone_auditorias');
    }
};
