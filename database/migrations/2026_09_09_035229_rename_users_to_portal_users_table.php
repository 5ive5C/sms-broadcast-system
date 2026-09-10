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
        Schema::rename('users', 'portal_users');

        Schema::table('portal_users', function (Blueprint $table) {
            $table->foreignId('client_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->nullable()->after('client_id')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('portal_users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('client_id');
            $table->dropConstrainedForeignId('role_id');
        });

        Schema::rename('portal_users', 'users');
    }
};
