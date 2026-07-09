<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('sla_rule_id')->nullable()->constrained('sla_rules')->nullOnDelete();
            $table->dateTime('first_response_due_at')->nullable();
            $table->dateTime('update_due_at')->nullable();
            $table->dateTime('solution_due_at')->nullable();
            $table->dateTime('escalated_at')->nullable();
            $table->boolean('is_escalated')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sla_rule_id');
            $table->dropColumn(['first_response_due_at', 'update_due_at', 'solution_due_at', 'escalated_at', 'is_escalated']);
        });
    }
};
