<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->text('client_brief')->nullable()->after('executive_summary');
            $table->timestamp('client_brief_updated_at')->nullable()->after('client_brief');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['client_brief', 'client_brief_updated_at']);
        });
    }
};
