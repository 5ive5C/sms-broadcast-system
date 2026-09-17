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
        Schema::table('messages', function (Blueprint $table) {
            $table->timestamp('scheduled_for')->nullable()->after('submitted_at');
            $table->timestamp('queued_at')->nullable()->after('scheduled_for');

            $table->index(['status', 'queued_at', 'scheduled_for']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex(['status', 'queued_at', 'scheduled_for']);
            $table->dropColumn(['scheduled_for', 'queued_at']);
        });
    }
};
