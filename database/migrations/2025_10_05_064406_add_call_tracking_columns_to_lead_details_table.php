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
        Schema::table('lead_details', function (Blueprint $table) {
            $table->timestamp('called_at')->nullable()->after('next_call_date');
            $table->unsignedBigInteger('assigned_to')->nullable()->after('created_by');

            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lead_details', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);
            $table->dropColumn(['called_at', 'assigned_to']);
        });
    }
};
