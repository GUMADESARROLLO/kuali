<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('software_licenses', function (Blueprint $table) {
            $table->boolean('recurring')->default(false)->after('seats_used');
            $table->string('billing_period')->nullable()->after('recurring')->comment('mensual, trimestral, semestral, anual');
        });
    }

    public function down(): void
    {
        Schema::table('software_licenses', function (Blueprint $table) {
            $table->dropColumn(['recurring', 'billing_period']);
        });
    }
};
