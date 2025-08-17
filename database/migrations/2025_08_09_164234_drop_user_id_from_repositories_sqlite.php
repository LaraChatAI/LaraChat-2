<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // SQLite doesn't support dropping columns easily, so we need to recreate the table
        if (config('database.default') === 'sqlite') {
            // Create new table without user_id
            Schema::create('repositories_new', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('url');
                $table->string('local_path');
                $table->string('branch')->default('main');
                $table->timestamp('last_pulled_at')->nullable();
                $table->timestamps();
            });
            
            // Copy data from old table to new table
            DB::statement('INSERT INTO repositories_new (id, name, slug, url, local_path, branch, last_pulled_at, created_at, updated_at) 
                          SELECT id, name, slug, url, local_path, branch, last_pulled_at, created_at, updated_at FROM repositories');
            
            // Drop old table
            Schema::dropIfExists('repositories');
            
            // Rename new table to repositories
            Schema::rename('repositories_new', 'repositories');
        } else {
            // For other databases, use standard approach
            Schema::table('repositories', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropIndex('repositories_user_url_unique');
                $table->dropColumn('user_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('repositories', function (Blueprint $table) {
            $table->foreignId('user_id')->after('id')->constrained()->onDelete('cascade');
            $table->unique(['user_id', 'url']);
        });
    }
};