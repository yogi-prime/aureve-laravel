<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create blog categories table first
        Schema::create('blog_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        
        // Create blog tags table
        Schema::create('blog_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        
        // Create blogs table
        Schema::create('blogs', function (Blueprint $table) {
            $table->id();
            
            // Basic Fields
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->string('featured_image')->nullable();
            $table->string('featured_image_alt')->nullable();
            
            // Category
            $table->foreignId('category_id')->nullable()->constrained('blog_categories')->nullOnDelete();
            
            // Status & Visibility
            $table->boolean('is_published')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->timestamp('published_at')->nullable();
            
            // Author
            $table->string('author_name')->nullable();
            $table->string('author_image')->nullable();
            $table->text('author_bio')->nullable();
            
            // Reading Time
            $table->integer('reading_time')->default(5);
            
            // SEO Fields - Basic
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->text('seo_keywords')->nullable();
            $table->string('canonical_url')->nullable();
            
            // SEO Fields - Open Graph
            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->string('og_image')->nullable();
            $table->string('og_type')->default('article');
            
            // SEO Fields - Twitter Card
            $table->string('twitter_card')->default('summary_large_image');
            $table->string('twitter_title')->nullable();
            $table->text('twitter_description')->nullable();
            $table->string('twitter_image')->nullable();
            
            // SEO Fields - Schema/JSON-LD
            $table->json('schema_markup')->nullable();
            
            // SEO Fields - Robots
            $table->boolean('robots_index')->default(true);
            $table->boolean('robots_follow')->default(true);
            
            // View Count
            $table->unsignedBigInteger('view_count')->default(0);
            
            $table->timestamps();
        });
        
        // Pivot table for blog-tag relationship
        Schema::create('blog_blog_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_id')->constrained()->cascadeOnDelete();
            $table->foreignId('blog_tag_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_blog_tag');
        Schema::dropIfExists('blogs');
        Schema::dropIfExists('blog_tags');
        Schema::dropIfExists('blog_categories');
    }
};
