<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained('workflows')->cascadeOnDelete();
            $table->text('caption')->nullable();
            $table->string('image_path')->nullable();
            $table->string('platform', 30)->default('facebook');
            $table->timestamp('posted_at')->nullable();
            $table->string('status', 30)->default('idle');
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['platform', 'status', 'posted_at']);
            $table->index(['workflow_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
