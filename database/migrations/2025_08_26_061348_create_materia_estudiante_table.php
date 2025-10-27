<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materia_estudiante', function (Blueprint $table) {
            $table->id();
            $table->decimal('nota', 3, 1)->nullable(); // 0.0 a 100.0
            $table->integer('creditos');
            $table->foreignId('materia_id')->constrained('materias')->onDelete('cascade');
            $table->foreignId('estudiante_id')->constrained('estudiantes')->onDelete('cascade');
            $table->foreignId('grupo_id')->constrained('grupos')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materia_estudiante');
    }
};
