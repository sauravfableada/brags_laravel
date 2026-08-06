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
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('vendor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->string('asin')->nullable();
            
            $table->string('primary_image')->nullable();
            $table->json('gallery_images')->nullable();
            $table->json('gallery_360_images')->nullable();
            
            // Content Restriction Settings
            $table->string('restriction_type')->default('default'); // default, message, redirect, template
            $table->json('restriction_display_for')->nullable();
            $table->json('restriction_purchase_for')->nullable();
            $table->boolean('enable_custom_messages')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['vendor_id']);
            $table->dropForeign(['brand_id']);
            $table->dropColumn([
                'vendor_id',
                'brand_id',
                'name',
                'slug',
                'short_description',
                'description',
                'asin',
                'primary_image',
                'gallery_images',
                'gallery_360_images',
                'restriction_type',
                'restriction_display_for',
                'restriction_purchase_for',
                'enable_custom_messages',
            ]);
        });
    }
};
