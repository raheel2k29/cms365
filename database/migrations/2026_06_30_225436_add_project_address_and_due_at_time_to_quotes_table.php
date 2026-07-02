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
        Schema::table('quotes', function (Blueprint $table) {
            $table->string('project_address')->nullable()->after('project_name');
            $table->dateTime('due_at')->nullable()->change();
            $table->date('expires_at')->nullable()->after('due_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn('project_address');
            $table->dropColumn('expires_at');
            $table->date('due_at')->nullable()->change();
        });
    }
};
