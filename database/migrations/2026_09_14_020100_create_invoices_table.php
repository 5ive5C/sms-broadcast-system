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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('top_up_request_id')->nullable()->constrained()->nullOnDelete();
            $table->string('invoice_no')->unique();
            $table->date('invoice_date');
            $table->unsignedInteger('credits');
            $table->decimal('price_per_credit', 8, 4);
            $table->decimal('amount', 14, 4);
            $table->decimal('sst', 14, 4)->default(0);
            $table->decimal('total', 14, 4);
            $table->enum('status', ['unpaid', 'paid'])->default('unpaid');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
