<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insights', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('client_id');
            $table->foreign('client_id')->references('id')->on('clients')->cascadeOnDelete();
            $table->enum('type', ['external', 'recommendation']);
            $table->string('title')->nullable();
            $table->json('content')->default('{}');
            $table->string('priority')->nullable(); // high, medium, low
            $table->string('category')->nullable();
            $table->boolean('dismissed')->default(false);
            $table->boolean('saved')->default(false);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insights');
    }
};
