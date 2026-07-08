<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_responses', function (Blueprint $table) {
            $table->text('reason')->nullable()->after('response');
            $table->text('fix_instruction')->nullable()->after('reason');
        });
    }

    public function down(): void
    {
        Schema::table('audit_responses', function (Blueprint $table) {
            $table->dropColumn(['reason', 'fix_instruction']);
        });
    }
};
