<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('dietary_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->collation('nocase')->unique();
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->string('color')->default('zinc');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dietary_tags');
    }
};
