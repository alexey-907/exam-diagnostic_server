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
        Schema::create('admission_tracks', function (Blueprint $table) {
            $table->id();
            $table->string('level', 10);
            $table->string('direction_code', 30)->nullable();
            $table->string('direction_title', 255);
            $table->jsonb('subject_set_rules');
            $table->text('source_url')->nullable();
            $table->timestamp('source_checked_at')->nullable();
            $table->integer('valid_year');


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admission_tracks');
    }
};
