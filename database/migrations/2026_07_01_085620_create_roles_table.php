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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('role',20);
            $table->string('login')->unique();
            $table->string('password');
            $table->timestamps();
        });

        DB::statement("ALTER TABLE roles ADD CONSTRAINT chk_role CHECK (role IN ('student', 'parent', 'teacher', 'admin'))");

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //Schema::dropIfExists('roles');
    }
};
