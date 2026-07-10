<?php
// database/migrations/2026_07_01_085706_create_subject_catalogs_table.php
// (обновленная версия)

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('subject_catalogs', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('title', 120);
            $table->string('exam_type', 10);
            $table->boolean('mandatory_flag')->default(false);
            $table->integer('active_from_year');
            $table->integer('active_to_year')->nullable();
            $table->text('source_url')->nullable();
            $table->timestamp('source_checked_at')->nullable();
            $table->timestamps();

            // Индексы
            $table->index('code');
            $table->index('exam_type');
            $table->index('mandatory_flag');
            $table->index('active_from_year');
            $table->index(['exam_type', 'mandatory_flag']);
        });

        // Добавляем CHECK constraint для exam_type
        DB::statement("ALTER TABLE subject_catalogs ADD CONSTRAINT chk_subject_exam_type CHECK (exam_type IN ('OGE', 'EGE', 'EARLY'))");

        // Добавляем CHECK constraint для active_from_year
        DB::statement('ALTER TABLE subject_catalogs ADD CONSTRAINT chk_subject_active_from_year CHECK (active_from_year >= 2027)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subject_catalogs');
    }
};
