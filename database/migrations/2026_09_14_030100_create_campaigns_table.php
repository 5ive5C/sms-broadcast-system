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
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('template_id')->nullable()->constrained('message_templates')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('launched_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->text('content');
            $table->enum('lane', ['transactional', 'bulk'])->default('bulk');
            $table->enum('status', ['draft', 'scheduled', 'sending', 'completed', 'cancelled'])->default('draft');
            $table->enum('recipient_source', ['paste', 'upload', 'merge'])->nullable();
            $table->string('recipient_file_name')->nullable();
            $table->json('pending_recipients')->nullable();
            $table->json('rejected_rows')->nullable();
            $table->json('merge_mapping')->nullable();
            $table->unsignedInteger('recipient_count')->default(0);
            $table->unsignedInteger('rejected_count')->default(0);
            $table->unsignedInteger('delivered_count')->default(0);
            $table->unsignedInteger('submitted_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->unsignedInteger('credits_required')->default(0);
            $table->unsignedInteger('credits_spent')->default(0);
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('launched_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
