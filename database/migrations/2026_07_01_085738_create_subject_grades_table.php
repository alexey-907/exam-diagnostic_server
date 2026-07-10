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
        Schema::create('subject_grades', function (Blueprint $table) {
            $table->id();

            $table->foreignId('session_id')->constrained('student_sessions')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subject_catalogs')->onDelete('cascade');

            $table->integer('quarter_grade')->nullable();
            $table->integer('annual_grade')->nullable();
            $table->integer('self_level')->nullable();

            $table->unique(['session_id', 'subject_id']);

        });
        DB::statement("ALTER TABLE subject_grades ADD CONSTRAINT chk_quarter_grade CHECK (quarter_grade BETWEEN 2 AND 5)");
        DB::statement("ALTER TABLE subject_grades ADD CONSTRAINT chk_annual_grade CHECK (annual_grade BETWEEN 2 AND 5)");
        DB::statement("ALTER TABLE subject_grades ADD CONSTRAINT chk_self_level CHECK (self_level BETWEEN 1 AND 5)");

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subject_grades');
    }
};
