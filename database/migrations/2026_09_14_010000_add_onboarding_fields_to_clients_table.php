<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('industry')->nullable()->after('name');
            $table->json('message_types')->nullable()->after('sender_id');
            $table->boolean('two_factor_required')->default(false)->after('message_types');
        });

        DB::statement("ALTER TABLE clients MODIFY status ENUM('active', 'suspended', 'inactive', 'pending') DEFAULT 'active'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("UPDATE clients SET status = 'inactive' WHERE status = 'pending'");
        DB::statement("ALTER TABLE clients MODIFY status ENUM('active', 'suspended', 'inactive') DEFAULT 'active'");

        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['industry', 'message_types', 'two_factor_required']);
        });
    }
};
