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
        Schema::table('categories', function (Blueprint $table) {
            $table->boolean('requires_approval')->default(false)->after('display_type');
            $table->string('content_restriction')->nullable()->after('requires_approval'); // e.g. 'none', 'logged_in', 'subscription'
            $table->text('restriction_message')->nullable()->after('content_restriction');
            $table->string('referral_rate_type')->nullable()->after('restriction_message'); // 'fixed' or 'percentage'
            $table->decimal('referral_rate', 10, 2)->nullable()->after('referral_rate_type');
            $table->boolean('disable_referrals')->default(false)->after('referral_rate');
            $table->string('category_icon')->nullable()->after('disable_referrals');
            $table->string('page_title_background')->nullable()->after('category_icon');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn([
                'requires_approval',
                'content_restriction',
                'restriction_message',
                'referral_rate_type',
                'referral_rate',
                'disable_referrals',
                'category_icon',
                'page_title_background',
            ]);
        });
    }
};
