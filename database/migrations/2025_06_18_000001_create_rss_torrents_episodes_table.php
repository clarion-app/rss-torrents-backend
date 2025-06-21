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
        Schema::create('rss_torrents_episodes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('series_id');
            $table->string('episode');
            $table->boolean('completed')->default(false);
            $table->text('hash_string')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign key constraint to link with series table
            $table->foreign('series_id')->references('id')->on('rss_torrents_series')->onDelete('cascade');
            
            // Index on series_id for better query performance
            $table->index('series_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rss_torrents_episodes');
    }
};
