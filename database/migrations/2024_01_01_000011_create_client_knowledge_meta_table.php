<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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

    public function down(): void
    {
        Schema::dropIfExists('client_knowledge_meta');
    }
};
