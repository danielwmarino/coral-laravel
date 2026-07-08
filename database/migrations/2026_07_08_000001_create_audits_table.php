<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('client_id');
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->string('product_name');
            $table->string('product_url')->nullable();
            $table->string('auditor_name')->nullable();
            $table->string('product_type'); // web_app / mobile_app / marketing_site / ecommerce / saas_dashboard
            $table->string('audit_mode')->default('manual'); // manual / ai_assisted
            $table->date('audit_date');
            $table->string('status')->default('in_progress'); // in_progress / completed
            $table->integer('ux_score')->nullable();
            $table->integer('content_score')->nullable();
            $table->integer('overall_score')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audits');
    }
};
