<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('log_importacion_vodafone', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('nombre_archivo')->nullable();
            $table->integer('cantidad_registros')->nullable();
            $table->json('errores_json')->nullable();
            $table->string('estado')->default('pendiente');
            $table->timestamps();
        });


        Schema::create('historial_registros_vodafone', function (Blueprint $table) {
            $table->id();

            // Quien creó el registro
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');

            // Subida a la que pertenece
            $table->foreignId('upload_id')->nullable()->constrained('log_importacion_vodafone')->onDelete('set null');

            // Encargado asignado
            $table->foreignId('asignado_a_id')->nullable()->constrained('users')->onDelete('set null');

            // Trazabilidad del registro (renombrado desde "estado")
            $table->enum('trazabilidad', [
                'pendiente',
                'asignado',
                'irrelevante',
                'completado',
                'retornado'
            ])->default('pendiente');

            // Datos nuevos solicitados
            $table->string('marca_base')->nullable();
            $table->string('origen_motivo_cancelacion')->nullable();
            $table->string('nombre_cliente')->nullable();
            $table->string('dni_cliente')->nullable()->unique();
            $table->string('orden_trabajo_anterior')->nullable();
            $table->string('telefono_principal')->nullable();
            $table->string('telefono_adicional')->nullable();
            $table->string('correo_referencia')->nullable();
            $table->string('direccion_historico')->nullable();
            $table->text('observaciones')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        // La migración de vodafone_auditorias se moverá a un archivo separado para asegurar el orden correcto de creación de tablas.
    }

    public function down(): void
    {
        // La migración de vodafone_auditorias está en un archivo separado.
        Schema::dropIfExists('historial_registros_vodafone');
        Schema::dropIfExists('log_importacion_vodafone');
    }
};
