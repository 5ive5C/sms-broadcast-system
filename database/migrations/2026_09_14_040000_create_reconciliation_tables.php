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
        Schema::create('reconciliation_runs', function (Blueprint $table) {
            $table->id();
            $table->date('run_date');
            $table->unsignedInteger('our_records')->default(0);
            $table->unsignedInteger('isms_report')->default(0);
            $table->decimal('matched_pct', 5, 2)->default(0);
            $table->unsignedInteger('discrepancy_count')->default(0);
            $table->timestamps();
        });

        Schema::create('reconciliation_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reconciliation_run_id')->constrained()->cascadeOnDelete();
            $table->foreignId('message_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('our_status');
            $table->string('isms_status');
            $table->string('resolution')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reconciliation_records');
        Schema::dropIfExists('reconciliation_runs');
    }
};
