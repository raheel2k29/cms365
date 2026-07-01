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
        Schema::table('companies', function (Blueprint $table) {
            $table->string('code')->nullable()->after('name');
            $table->boolean('is_active')->default(true)->after('industry');
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->string('department')->nullable()->after('position');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['code', 'is_active']);
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn('department');
        });
    }
};
