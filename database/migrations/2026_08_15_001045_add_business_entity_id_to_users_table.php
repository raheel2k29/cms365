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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('business_entity_id')->nullable()->constrained('business_entities')->nullOnDelete();
        });

        // Ensure a default business entity exists
        $entity = \App\Models\BusinessEntity::first();
        if (!$entity) {
            $entity = \App\Models\BusinessEntity::create([
                'name' => 'Electric Supply Connections',
                'code' => 'ESC'
            ]);
        }
        
        // Assign all existing users to the default business entity
        \App\Models\User::whereNull('business_entity_id')->update(['business_entity_id' => $entity->id]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['business_entity_id']);
            $table->dropColumn('business_entity_id');
        });
    }
};
