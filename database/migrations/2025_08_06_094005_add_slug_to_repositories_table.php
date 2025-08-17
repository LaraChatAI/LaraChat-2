<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('repositories', function (Blueprint $table) {
            $table->string('slug')->after('name')->nullable();
        });
        
        // Generate slugs for existing repositories
        $repositories = DB::table('repositories')->get();
        foreach ($repositories as $repository) {
            $slug = Str::slug($repository->name);
            // Ensure uniqueness
            $originalSlug = $slug;
            $count = 1;
            while (DB::table('repositories')->where('slug', $slug)->where('id', '!=', $repository->id)->exists()) {
                $slug = $originalSlug . '-' . $count;
                $count++;
            }
            DB::table('repositories')
                ->where('id', $repository->id)
                ->update(['slug' => $slug]);
        }
        
        // Make slug not nullable and unique after populating
        Schema::table('repositories', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('repositories', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
