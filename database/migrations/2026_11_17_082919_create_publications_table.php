<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('publications', function (Blueprint $table) {
            $table->id();
            $table->string('number', 30)->nullable()->unique();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('team_id')->constrained()->restrictOnDelete();
            $table->string('type', 10);
            $table->foreignId('arc_publication_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('title_en')->nullable();
            $table->date('release_date');
            $table->string('frequency', 30);
            $table->string('language', 30);
            $table->unsignedSmallInteger('year')->nullable();
            $table->unsignedSmallInteger('pages_romawi')->nullable();
            $table->unsignedSmallInteger('pages_arabic')->nullable();
            $table->boolean('has_cooperation')->nullable();
            $table->string('published_for', 30)->nullable();
            $table->string('cover_maker', 30)->nullable();
            $table->string('orientation', 20)->nullable();
            $table->string('paper_size', 20)->nullable();
            $table->string('status', 30)->default('diajukan');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publications');
    }
};