<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artifacts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('inventory_number', 32)->unique();
            $table->string('period')->nullable();
            $table->string('material')->nullable();
            $table->string('condition_state');
            $table->string('acquisition_type', 16);
            $table->string('status', 24)->default('in_storage')->index();
            $table->decimal('appraised_value', 12, 2)->default(0);
            $table->decimal('sale_price', 12, 2)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artifacts');
    }
};
