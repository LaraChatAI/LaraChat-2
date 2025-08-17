<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('github_webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event_type');
            $table->string('delivery_id')->nullable()->index();
            $table->string('repository')->nullable();
            $table->json('payload');
            $table->enum('status', ['success', 'failed', 'processing'])->default('processing');
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            $table->index(['event_type', 'created_at']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('github_webhook_logs');
    }
};