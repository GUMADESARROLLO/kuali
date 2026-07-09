<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_tag');
            $table->string('name');
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('asset_category_id')->constrained('asset_categories')->restrictOnDelete();
            $table->foreignId('parent_asset_id')->nullable()->constrained('assets')->nullOnDelete();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('status')->default('disponible');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('location')->nullable();
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_cost', 10, 2)->nullable();
            $table->date('warranty_expiry')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'asset_tag']);
        });

        Schema::create('asset_maintenance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->string('maintenance_type');
            $table->string('component')->nullable();
            $table->text('description');
            $table->decimal('cost', 10, 2)->nullable();
            $table->string('performed_by')->nullable();
            $table->dateTime('performed_at');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('software_licenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('license_key')->nullable();
            $table->string('vendor')->nullable();
            $table->integer('seats_total')->default(1);
            $table->integer('seats_used')->default(0);
            $table->date('purchase_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->decimal('cost', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('asset_software', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('software_license_id')->constrained()->cascadeOnDelete();
            $table->dateTime('installed_at')->nullable();
            $table->dateTime('uninstalled_at')->nullable();
            $table->timestamps();

            $table->unique(['asset_id', 'software_license_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_software');
        Schema::dropIfExists('software_licenses');
        Schema::dropIfExists('asset_maintenance');
        Schema::dropIfExists('assets');
    }
};
