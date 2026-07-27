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
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Step 1
            $table->string('brand_name');
            $table->string('brand_logo');
            $table->string('trademark_office');
            $table->string('trademark_registration_number');
            $table->text('brand_description');
            
            // Step 2 (Business info)
            $table->string('business_name');
            $table->text('business_address');
            $table->string('business_contact_email');
            $table->string('primary_contact_name');
            $table->string('phone_number_country')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('website_url')->nullable();
            
            // Step 3 (Manufacturing & Distribution)
            $table->text('manufacturing_locations');
            $table->text('distribution_channels');
            $table->text('authorized_resellers')->nullable();
            $table->text('product_supply_chain')->nullable();
            
            // Step 4 (Product Information)
            $table->string('product_categories');
            
            // Step 5 (Brand Protection)
            $table->boolean('sell_under_own_brand')->default(true);
            $table->string('seller_email')->nullable();
            $table->string('store_url')->nullable();
            $table->boolean('approve_resellers')->default(false);
            $table->string('additional_documentation')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brands');
    }
};
