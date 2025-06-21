<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rss_torrents_series', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->index();
            $table->string('title');
            $table->string('feed_url');
            $table->boolean('subscribed')->default(false);
            $table->timestamps();
            $table->softDeletes();
            
            // Unique constraint on name and feed_url combination
            $table->unique(['name', 'feed_url']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rss_torrents_series');
    }
};
