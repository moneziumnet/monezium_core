<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePagesettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pagesettings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('contact_success', 191);
            $table->string('contact_email', 191);
            $table->text('contact_title')->nullable();
            $table->text('contact_text')->nullable();
            $table->text('side_title')->nullable();
            $table->text('side_text')->nullable();
            $table->text('street')->nullable();
            $table->text('phone')->nullable();
            $table->text('fax')->nullable();
            $table->text('email')->nullable();
            $table->text('site')->nullable();
            $table->boolean('slider')->default(true);
            $table->boolean('service')->default(true);
            $table->boolean('featured')->default(true);
            $table->boolean('small_banner')->default(true);
            $table->boolean('best')->default(true);
            $table->boolean('top_rated')->default(true);
            $table->boolean('large_banner')->default(true);
            $table->boolean('big')->default(true);
            $table->boolean('hot_sale')->default(true);
            $table->string('hero_title')->nullable();
            $table->string('hero_subtitle')->nullable();
            $table->string('hero_btn_url')->nullable();
            $table->string('hero_link')->nullable();
            $table->string('hero_photo')->nullable();
            $table->boolean('review_blog')->default(true);
            $table->boolean('pricing_plan')->default(false);
            $table->text('service_subtitle')->nullable();
            $table->text('service_title')->nullable();
            $table->text('service_text')->nullable();
            $table->string('plan_title')->nullable();
            $table->text('plan_subtitle')->nullable();
            $table->text('review_subtitle')->nullable();
            $table->text('review_title')->nullable();
            $table->text('review_text')->nullable();
            $table->string('quick_title')->nullable();
            $table->string('quick_subtitle')->nullable();
            $table->string('quick_link')->nullable();
            $table->string('quick_photo')->nullable();
            $table->string('quick_background')->nullable();
            $table->text('blog_subtitle')->nullable();
            $table->text('blog_title')->nullable();
            $table->text('blog_text')->nullable();
            $table->string('faq_title')->nullable();
            $table->text('faq_subtitle')->nullable();
            $table->string('about_photo')->nullable();
            $table->string('about_title')->nullable();
            $table->text('about_text')->nullable();
            $table->mediumText('about_attributes')->nullable();
            $table->string('about_link')->nullable();
            $table->mediumText('about_details')->nullable();
            $table->string('service_photo')->nullable();
            $table->text('service_video')->nullable();
            $table->string('strategy_title')->nullable();
            $table->text('strategy_details')->nullable();
            $table->string('strategy_banner')->nullable();
            $table->string('footer_top_photo')->nullable();
            $table->string('footer_top_title')->nullable();
            $table->string('footer_top_text')->nullable();
            $table->text('banner_title')->nullable();
            $table->text('banner_text')->nullable();
            $table->text('banner_link1')->nullable();
            $table->text('banner_link2')->nullable();
            $table->string('app_banner')->nullable();
            $table->string('app_title')->nullable();
            $table->mediumText('app_details')->nullable();
            $table->string('app_store_photo')->nullable();
            $table->string('app_store_link')->nullable();
            $table->string('app_google_store')->nullable();
            $table->string('app_google_link')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pagesettings');
    }
}
