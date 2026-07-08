<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_responses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('audit_id');
            $table->foreign('audit_id')->references('id')->on('audits')->onDelete('cascade');
            $table->string('section'); // ux / content
            $table->string('category');
            $table->string('item_key');
            $table->string('response')->nullable(); // yes / no / na / fail
            $table->timestamps();

            $table->unique(['audit_id', 'section', 'item_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_responses');
    }
};
