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
        Schema::create('test_items', function (Blueprint $table) {
            $table->id();
            $table->string('test_code', 40);
            $table->string('item_code', 40);
            $table->text('item_text');

            $table->jsonb('options_json')->nullable();
            $table->jsonb('correct_answer_json')->nullable();

            $table->decimal('max_score', 5, 2)->default(1);
            $table->boolean('active')->default(true);            $table->timestamps();

            $table->integer('difficulty')->nullable();
        });
        DB::statement("ALTER TABLE test_items ADD CONSTRAINT chk_difficulty CHECK (difficulty BETWEEN 1 AND 5)");

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_items');
    }
};
