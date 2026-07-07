<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Enable pgvector extension
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

        // Add vector column separately (pgvector type not in Blueprint)
        DB::statement('ALTER TABLE knowledge_chunks ADD COLUMN embedding vector(1536)');
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_chunks');
    }
};
