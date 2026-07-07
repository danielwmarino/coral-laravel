<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_ai_preferences', function (Blueprint $table) {
            $table->uuid('client_id')->primary();
            $table->foreign('client_id')->references('id')->on('clients')->cascadeOnDelete();
            $table->string('preferred_provider')->nullable();
            $table->foreign('preferred_provider')->references('provider')->on('ai_provider_connections')->nullOnDelete();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_ai_preferences');
    }
};
