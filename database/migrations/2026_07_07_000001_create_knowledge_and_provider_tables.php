<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // knowledge_chunks
        if (!Schema::hasTable('knowledge_chunks')) {
            DB::statement('CREATE EXTENSION IF NOT EXISTS vector');
            Schema::create('knowledge_chunks', function (Blueprint $table) {
                $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
                $table->uuid('client_id');
                $table->foreign('client_id')->references('id')->on('clients')->cascadeOnDelete();
                $table->enum('source_type', ['document', 'website', 'platform']);
                $table->string('source_label');
                $table->text('chunk_text');
                $table->timestamp('created_at')->useCurrent();
            });
            DB::statement('ALTER TABLE knowledge_chunks ADD COLUMN IF NOT EXISTS embedding vector(1536)');
        }

        // client_knowledge_meta
        if (!Schema::hasTable('client_knowledge_meta')) {
            Schema::create('client_knowledge_meta', function (Blueprint $table) {
                $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
                $table->uuid('client_id')->unique();
                $table->foreign('client_id')->references('id')->on('clients')->cascadeOnDelete();
                $table->string('website_url')->nullable();
                $table->timestamp('last_crawled_at')->nullable();
                $table->string('crawl_status')->default('idle');
                $table->integer('crawl_page_count')->default(0);
                $table->text('summary_text')->nullable();
                $table->timestamp('summary_updated_at')->nullable();
                $table->timestamps();
            });
        }

        // ai_provider_connections
        if (!Schema::hasTable('ai_provider_connections')) {
            Schema::create('ai_provider_connections', function (Blueprint $table) {
                $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
                $table->string('provider')->unique();
                $table->string('label');
                $table->string('encrypted_key');
                $table->string('key_preview');
                $table->foreignId('added_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        // client_ai_preferences
        if (!Schema::hasTable('client_ai_preferences')) {
            Schema::create('client_ai_preferences', function (Blueprint $table) {
                $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
                $table->uuid('client_id')->unique();
                $table->foreign('client_id')->references('id')->on('clients')->cascadeOnDelete();
                $table->string('default_provider')->default('anthropic');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('client_ai_preferences');
        Schema::dropIfExists('ai_provider_connections');
        Schema::dropIfExists('client_knowledge_meta');
        Schema::dropIfExists('knowledge_chunks');
    }
};
