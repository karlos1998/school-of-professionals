<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('exam_authority_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_class_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 160);
            $table->string('slug', 180)->unique();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['exam_authority_id', 'exam_category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
