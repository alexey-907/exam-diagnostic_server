<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cognitive_results', function (Blueprint $table) {
            $table->id();

            $table->foreignId('session_id')->constrained('student_sessions')->onDelete('cascade');
            $table->string('test_code', 40);
            $table->decimal('raw_score', 6, 2);
            $table->integer('normalized_score');
            $table->string('level_code', 20);
            $table->text('interpretation')->nullable();

            $table->unique(['session_id', 'test_code']);
        });

        DB::statement("ALTER TABLE cognitive_results ADD CONSTRAINT chk_normalized_score CHECK (normalized_score BETWEEN 0 AND 100)");

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cognitive_results');
    }
};
