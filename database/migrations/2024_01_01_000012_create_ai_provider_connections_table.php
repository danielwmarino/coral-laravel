<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_provider_connections', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->string('provider')->unique(); // e.g. "anthropic", "openai", "gemini"
            $table->string('label');              // e.g. "Claude (Anthropic)"
            $table->string('encrypted_key');      // AES-256-GCM encrypted API key
            $table->string('key_preview');        // masked e.g. "sk-...ab12"
            $table->foreignId('added_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_provider_connections');
    }
};
