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
        $allowedMediaType = config('site.allowedMediaType');
        $allowedMediaRole = config('site.allowedMediaRole');

        Schema::create('media', function (Blueprint $table) use ($allowedMediaType, $allowedMediaRole) {
            $table->id();
            $table->string('name');
            $table->enum('type', $allowedMediaType);
            $table->string('path');
            $table->string('media_type')->nullable();
            $table->string('media_id')->nullable();
            $table->enum('media_role', $allowedMediaRole)
                ->default($allowedMediaRole[0])
                ->comment('Media Usage Type');
            $table->string('size')->nullable();
            $table->string('mime')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
