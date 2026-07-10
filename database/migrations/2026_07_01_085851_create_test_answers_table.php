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
        Schema::create('test_answers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('session_id')->constrained('student_sessions')->onDelete('cascade');
            $table->foreignId('test_item_id')->constrained('test_items');

            $table->jsonb('user_answer_json');
            $table->decimal('score', 5, 2)->nullable();
            $table->timestamp('checked_at')->nullable();

            $table->unique(['session_id', 'test_item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_answers');
    }
};
