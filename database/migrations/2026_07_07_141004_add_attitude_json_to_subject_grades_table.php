<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subject_grades', function (Blueprint $table) {
            // Добавляем текстовое или JSON поле для хранения ответов анкеты S4
            $table->text('attitude_json')->nullable()->after('annual_grade');
        });
    }

    public function down(): void
    {
        Schema::table('subject_grades', function (Blueprint $table) {
            $table->dropColumn('attitude_json');
        });
    }
};
