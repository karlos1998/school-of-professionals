<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_categories', function (Blueprint $table): void {
            $table->boolean('is_favorite')->default(false)->after('slug');
        });
    }

    public function down(): void
    {
        Schema::table('exam_categories', function (Blueprint $table): void {
            $table->dropColumn('is_favorite');
        });
    }
};
