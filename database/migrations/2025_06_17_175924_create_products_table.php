<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->index();
            $table->integer('quantity')->unsigned()->index();
            $table->decimal('price', 10, 2)->unsigned()->index();
            $table->decimal('total_value', 12, 2)->unsigned()->index();
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['created_at', 'deleted_at']);
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};