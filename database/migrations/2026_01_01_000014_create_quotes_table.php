<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->string('quote_number')->unique();
            $table->foreignId('business_entity_id')->constrained('business_entities');
            $table->foreignId('quote_type_id')->nullable()->constrained('quote_types')->nullOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('project_name')->nullable();
            $table->string('status')->default('new');
            // new | in_review | rfq_sent | pricing_received | quote_prepared | quote_sent | won | lost | cancelled
            $table->string('currency', 10)->default('USD');
            $table->string('source')->default('manual'); // manual | email
            $table->string('quickbooks_ref')->nullable();
            $table->date('requested_at')->nullable();
            $table->date('due_at')->nullable();
            $table->date('quote_sent_at')->nullable();
            $table->date('won_lost_at')->nullable();
            $table->text('lost_reason')->nullable();
            $table->decimal('total_cost', 12, 2)->default(0);
            $table->decimal('total_sell', 12, 2)->default(0);
            $table->decimal('gross_margin_amount', 12, 2)->default(0);
            $table->decimal('gross_margin_pct', 8, 2)->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
