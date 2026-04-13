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
        Schema::create('articles', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('series_id')->nullable()->constrained()->nullOnDelete();

            // Contenu
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt');
            $table->json('content'); // Format TipTap
            $table->integer('reading_time')->default(0);

            // Couverture
            $table->string('cover_image')->nullable();
            $table->json('cover_gallery')->nullable();
            $table->enum('cover_type', ['image', 'gallery'])->default('image');

            // SEO
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('canonical_url')->nullable();

            // Statut & publication
            $table->enum('status', ['draft', 'published', 'scheduled'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->string('preview_token')->nullable()->unique();

            // Ordre dans une série
            $table->integer('series_order')->nullable();

            // Stats dénormalisées
            $table->unsignedInteger('views_count')->default(0);
            $table->unsignedInteger('likes_count')->default(0);
            $table->unsignedInteger('comments_count')->default(0);

            $table->timestamps();
            $table->softDeletes(); // Nécessaire pour $table->softDeletes()

            // Index pour la performance
            $table->index(['status', 'published_at']);
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
