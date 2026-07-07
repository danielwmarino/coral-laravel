<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_connections', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('client_id');
            $table->foreign('client_id')->references('id')->on('clients')->cascadeOnDelete();
            $table->enum('platform', ['google_analytics', 'google_search_console', 'facebook_ads', 'linkedin_ads', 'semrush']);
            $table->text('oauth_token')->nullable();
            $table->json('config')->default('{}');
            $table->timestamp('connected_at')->useCurrent();
            $table->timestamp('last_synced_at')->nullable();
            $table->unique(['client_id', 'platform']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_connections');
    }
};
