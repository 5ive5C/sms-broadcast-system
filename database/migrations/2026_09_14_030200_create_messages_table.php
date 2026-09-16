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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('campaign_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('api_key_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('lane', ['tac', 'transactional', 'bulk']);
            $table->string('recipient', 20);
            $table->text('content');
            $table->enum('encoding', ['gsm7', 'unicode'])->default('gsm7');
            $table->unsignedTinyInteger('parts')->default(1);
            $table->enum('status', ['submitted', 'delivered', 'failed'])->default('submitted');
            $table->unsignedTinyInteger('credit_charged')->default(1);
            $table->unsignedTinyInteger('credit_refunded')->default(0);
            $table->string('failure_reason')->nullable();
            $table->string('isms_status')->nullable();
            $table->timestamp('submitted_at');
            $table->timestamp('final_at')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'lane', 'status']);
            $table->index(['client_id', 'status', 'submitted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
