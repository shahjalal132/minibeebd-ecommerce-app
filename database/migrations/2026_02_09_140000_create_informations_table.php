<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('informations', function (Blueprint $table) {
            $table->id();
            $table->string('site_name')->nullable();
            $table->string('site_logo')->nullable();
            $table->string('fav_icon')->nullable();
            $table->string('owner_phone')->nullable();
            $table->string('owner_email')->nullable();
            $table->text('address')->nullable();
            $table->string('copyright')->nullable();
            $table->text('topbar_notice')->nullable();
            
            // Social Media
            $table->string('facebook')->nullable();
            $table->string('instagram')->nullable();
            $table->string('tiktok')->nullable();
            $table->string('twitter')->nullable();
            $table->string('youtube')->nullable();
            
            // Settings
            $table->text('tracking_code')->nullable();
            $table->integer('recommend_num')->nullable()->default(10);
            $table->integer('discount_num')->nullable()->default(10);
            $table->integer('newarrival_num')->nullable()->default(10);
            
            // Payment Integrations
            $table->string('bkash')->nullable();
            $table->string('bkash_number')->nullable();
            $table->string('nogod')->nullable();
            $table->string('nogod_number')->nullable();
            $table->string('rocket')->nullable();
            $table->string('rocket_number')->nullable();
            $table->string('paypal')->nullable();
            $table->string('paypal_account')->nullable();
            $table->string('stripe')->nullable();
            $table->string('stripe_account')->nullable();
            
            // Communications
            $table->string('whats_num')->nullable();
            $table->string('whats_active')->nullable();
            $table->text('msngr_chat')->nullable();
            $table->text('msngr_plugin')->nullable();
            $table->string('supp_num1')->nullable();
            $table->string('supp_num2')->nullable();
            $table->string('supp_num3')->nullable();
            $table->string('number_visibility')->nullable();
            $table->string('coupon_visibility')->nullable();
            
            // Currencies and APIs
            $table->string('currency')->nullable()->default('BDT');
            $table->string('redx_api_base_url')->nullable();
            $table->string('redx_api_access_token')->nullable();
            $table->string('pathao_api_base_url')->nullable();
            $table->text('pathao_api_access_token')->nullable();
            $table->string('pathao_store_id')->nullable();
            $table->string('steadfast_api_base_url')->nullable();
            $table->string('steadfast_api_key')->nullable();
            $table->string('steadfast_secret_key')->nullable();
            
            // Other settings
            $table->string('fb_pixel_id')->nullable();
            $table->text('fb_pixel_test_code')->nullable();
            $table->text('fb_access_token')->nullable();
            $table->string('fraudApi')->nullable();
            $table->string('pathao_status')->nullable();
            $table->string('redx_status')->nullable();
            $table->string('is_ip_check')->nullable();
            $table->string('is_mobile_check')->nullable();
            $table->string('time_limit')->nullable();
            
            // Design/UI settings
            $table->string('primary_color')->nullable();
            $table->string('primary_background')->nullable();
            $table->string('primary_background2')->nullable();
            $table->string('primary_background3')->nullable();
            $table->text('gradient_code')->nullable();
        });

        // Insert a default row so that Information::first() never returns null
        DB::table('informations')->insert([
            'site_name' => 'MiniBee',
            'currency' => 'BDT'
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('informations');
    }
};
