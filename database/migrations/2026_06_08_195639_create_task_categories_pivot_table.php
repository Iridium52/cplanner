<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_categories', function (Blueprint $table) {
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_category_id')->constrained('project_categories')->cascadeOnDelete();
            $table->primary(['task_id', 'project_category_id']);
        });

        // Migrate existing single category_id relationships into the pivot table
        DB::statement('
            INSERT INTO task_categories (task_id, project_category_id)
            SELECT id, category_id FROM tasks WHERE category_id IS NOT NULL
        ');

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->constrained('project_categories')->nullOnDelete();
        });

        // Restore one category per task (take the first if multiple)
        DB::statement('
            UPDATE tasks t
            JOIN task_categories tc ON tc.task_id = t.id
            SET t.category_id = tc.project_category_id
            WHERE t.category_id IS NULL
        ');

        Schema::dropIfExists('task_categories');
    }
};
