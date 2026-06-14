<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nama', 100)->after('id')->nullable();
            $table->string('peran')->after('nama')->nullable();
            $table->string('npm_nidn', 30)->after('peran')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nama', 'peran', 'npm_nidn']);
        });
    }
};