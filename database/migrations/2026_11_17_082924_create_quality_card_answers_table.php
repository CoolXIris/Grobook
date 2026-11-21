<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quality_card_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quality_card_id')->constrained()->cascadeOnDelete();
            $table->foreignId('checklist_item_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['quality_card_id', 'checklist_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quality_card_answers');
    }
};