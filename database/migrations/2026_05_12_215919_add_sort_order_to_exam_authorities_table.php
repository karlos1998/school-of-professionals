<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_authorities', function (Blueprint $table): void {
            $table->unsignedInteger('sort_order')->default(0)->after('slug');
            $table->index('sort_order');
        });

        DB::table('exam_authorities')
            ->orderBy('name')
            ->get(['id'])
            ->each(function (object $authority, int $index): void {
                DB::table('exam_authorities')
                    ->where('id', $authority->id)
                    ->update(['sort_order' => $index + 1]);
            });
    }

    public function down(): void
    {
        Schema::table('exam_authorities', function (Blueprint $table): void {
            $table->dropIndex(['sort_order']);
            $table->dropColumn('sort_order');
        });
    }
};
