<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('goal_analytics_links', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('goal_id');
            $table->foreign('goal_id')->references('id')->on('goals')->cascadeOnDelete();
            $table->uuid('analytics_connection_id');
            $table->foreign('analytics_connection_id')->references('id')->on('analytics_connections')->cascadeOnDelete();
            $table->string('metric_key');
            $table->decimal('target_value', 15, 4)->nullable();
            $table->decimal('current_value', 15, 4)->default(0);
            $table->timestamp('last_updated')->useCurrent();
            $table->unique(['goal_id', 'analytics_connection_id', 'metric_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goal_analytics_links');
    }
};
