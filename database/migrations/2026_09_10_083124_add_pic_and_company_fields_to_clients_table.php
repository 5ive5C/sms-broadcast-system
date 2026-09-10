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
        Schema::table('clients', function (Blueprint $table) {
            $table->string('company_reg_no')->nullable()->after('name');
            $table->text('address')->nullable()->after('company_reg_no');
            $table->string('pic_name')->nullable()->after('address');
            $table->string('pic_phone')->nullable()->after('pic_name');
            $table->string('pic_email')->nullable()->after('pic_phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['company_reg_no', 'address', 'pic_name', 'pic_phone', 'pic_email']);
        });
    }
};
