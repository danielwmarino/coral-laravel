<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('goals', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('client_id');
            $table->foreign('client_id')->references('id')->on('clients')->cascadeOnDelete();
            $table->uuid('strategy_id')->nullable();
            $table->foreign('strategy_id')->references('id')->on('strategies')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('smart_details')->default('{}');
            $table->enum('status', ['not_started', 'in_progress', 'completed', 'at_risk'])->default('not_started');
            $table->decimal('target_value', 15, 4)->nullable();
            $table->decimal('current_value', 15, 4)->default(0);
            $table->enum('metric_type', ['number', 'percentage', 'currency', 'rank'])->default('number');
            $table->date('due_date')->nullable();
            $table->text('strategist_notes')->nullable();
            $table->boolean('archived')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goals');
    }
};
