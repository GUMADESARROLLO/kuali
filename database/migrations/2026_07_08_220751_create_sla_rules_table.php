<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sla_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('calendar_id')->constrained()->cascadeOnDelete();
            $table->json('conditions')->nullable()->comment('{department_id: [...], category_id: [...], priority: [...]}');
            $table->unsignedSmallInteger('first_response_hours')->default(0)->comment('0 = no aplica');
            $table->unsignedSmallInteger('update_hours')->default(0);
            $table->unsignedSmallInteger('solution_hours')->default(0);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sla_rules');
    }
};
