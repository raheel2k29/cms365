<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $entities = DB::table('business_entities')->get();
        
        $statuses = [
            ['name' => 'Standby', 'color' => '#E0D4F6'],
            ['name' => 'Takeoff Done', 'color' => '#FAD2B8'],
            ['name' => 'Not Requested', 'color' => '#FAD2D2'],
            ['name' => 'Requested', 'color' => '#C4E1F6'],
            ['name' => 'Quote ready', 'color' => '#F3E500'],
            ['name' => 'Complete', 'color' => '#D1F0D1'],
            ['name' => 'Missed', 'color' => '#FAD2D2'],
            ['name' => 'No BID', 'color' => '#E5E5E5'],
            ['name' => 'Pending - Customer Info', 'color' => '#B30000'],
            ['name' => 'New', 'color' => '#3b82f6'],
            ['name' => 'In Review', 'color' => '#f59e0b'],
            ['name' => 'Quote Sent', 'color' => '#10b981'],
            ['name' => 'Won', 'color' => '#22c55e'],
            ['name' => 'Lost', 'color' => '#ef4444'],
            ['name' => 'Cancelled', 'color' => '#6b7280'],
        ];

        foreach ($entities as $entity) {
            foreach ($statuses as $index => $status) {
                DB::table('quote_statuses')->insert([
                    'business_entity_id' => $entity->id,
                    'name' => $status['name'],
                    'color' => $status['color'],
                    'order_index' => $index,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Migrate existing quotes
        $quotes = DB::table('quotes')->get();
        foreach ($quotes as $quote) {
            // Try to find a matching new status based on the old status string
            $oldStatus = $quote->status;
            $searchName = 'New';
            
            if ($oldStatus === 'in_review') $searchName = 'In Review';
            elseif ($oldStatus === 'quote_sent') $searchName = 'Quote Sent';
            elseif ($oldStatus === 'won') $searchName = 'Won';
            elseif ($oldStatus === 'lost') $searchName = 'Lost';
            elseif ($oldStatus === 'cancelled') $searchName = 'Cancelled';
            
            $statusObj = DB::table('quote_statuses')
                ->where('business_entity_id', $quote->business_entity_id)
                ->where('name', $searchName)
                ->first();
                
            if ($statusObj) {
                DB::table('quotes')
                    ->where('id', $quote->id)
                    ->update(['quote_status_id' => $statusObj->id]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse for data migration
    }
};
