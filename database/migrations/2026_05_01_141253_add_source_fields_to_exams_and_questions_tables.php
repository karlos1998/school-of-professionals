<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table): void {
            $table->string('source', 20)->nullable()->after('id');
            $table->string('source_slug', 200)->nullable()->after('slug');

            $table->unique(['source', 'source_slug']);
        });

        Schema::table('questions', function (Blueprint $table): void {
            $table->string('image_path')->nullable()->after('content');
            $table->string('source_question_key', 80)->nullable()->after('position');

            $table->unique(['exam_id', 'source_question_key']);
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table): void {
            $table->dropUnique('questions_exam_id_source_question_key_unique');
            $table->dropColumn(['image_path', 'source_question_key']);
        });

        Schema::table('exams', function (Blueprint $table): void {
            $table->dropUnique('exams_source_source_slug_unique');
            $table->dropColumn(['source', 'source_slug']);
        });
    }
};
