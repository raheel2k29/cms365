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
        Schema::table('contacts', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable()->change();
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->foreignId('rep_agency_id')->nullable()->constrained('rep_agencies')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable(false)->change();
            $table->dropForeign(['vendor_id']);
            $table->dropColumn('vendor_id');
            $table->dropForeign(['rep_agency_id']);
            $table->dropColumn('rep_agency_id');
        });
    }
};
