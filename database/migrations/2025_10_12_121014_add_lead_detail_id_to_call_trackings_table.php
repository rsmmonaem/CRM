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
        Schema::table('call_trackings', function (Blueprint $table) {
            $table->foreignId('lead_detail_id')->nullable()->constrained()->onDelete('set null');
            $table->index(['lead_detail_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('call_trackings', function (Blueprint $table) {
            $table->dropForeign(['lead_detail_id']);
            $table->dropIndex(['lead_detail_id']);
            $table->dropColumn('lead_detail_id');
        });
    }
};
