<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGeneralsettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('generalsettings', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('logo', 191)->nullable();
            $table->string('favicon', 191);
            $table->string('loader', 191);
            $table->string('admin_loader', 191)->nullable();
            $table->string('banner', 191)->nullable();
            $table->string('title', 191);
            $table->string('account_no_prefix')->nullable();
            $table->string('wallet_no_prefix')->nullable();
            $table->text('header_email')->nullable();
            $table->text('header_phone')->nullable();
            $table->text('footer');
            $table->text('copyright');
            $table->string('colors', 191)->nullable();
            $table->boolean('is_talkto')->default(true);
            $table->text('talkto')->nullable();
            $table->boolean('is_language')->default(true);
            $table->boolean('is_loader')->default(true);
            $table->text('map_key')->nullable();
            $table->boolean('is_disqus')->default(false);
            $table->text('disqus')->nullable();
            $table->boolean('is_contact')->default(false);
            $table->boolean('is_faq')->default(false);
            $table->tinyInteger('withdraw_status')->default(0);
            $table->string('smtp_host', 191);
            $table->string('smtp_port', 191);
            $table->string('smtp_encryption')->nullable();
            $table->string('smtp_user', 191);
            $table->string('smtp_pass', 191);
            $table->string('from_email', 191);
            $table->string('from_name', 191);
            $table->boolean('is_smtp')->default(false);
            $table->text('coupon_found')->nullable();
            $table->text('already_coupon')->nullable();
            $table->text('order_title')->nullable();
            $table->text('service_subtitle')->nullable();
            $table->text('service_title')->nullable();
            $table->text('service_text')->nullable();
            $table->string('service_image', 191)->nullable();
            $table->text('order_text')->nullable();
            $table->boolean('is_currency')->default(false);
            $table->boolean('currency_format')->default(false);
            $table->text('price_bigtitle')->nullable();
            $table->text('price_title');
            $table->text('price_subtitle');
            $table->text('price_text');
            $table->text('subscribe_success')->nullable();
            $table->text('subscribe_error')->nullable();
            $table->text('error_title')->nullable();
            $table->text('error_text')->nullable();
            $table->string('error_photo', 191)->nullable();
            $table->string('breadcumb_banner', 191)->nullable();
            $table->boolean('is_admin_loader')->default(false);
            $table->string('currency_code', 191)->nullable();
            $table->string('currency_sign', 191)->nullable();
            $table->boolean('is_verification_email')->default(false);
            $table->boolean('is_affilate')->default(true);
            $table->double('affilate_charge')->default(0);
            $table->text('affilate_banner')->nullable();
            $table->string('secret_string')->nullable();
            $table->integer('gap_limit')->default(300);
            $table->tinyInteger('isWallet')->default(0);
            $table->integer('affilate_new_user')->default(0);
            $table->integer('affilate_user')->default(0);
            $table->string('footer_logo', 191)->nullable();
            $table->string('pm_account', 191)->nullable();
            $table->tinyInteger('is_pm')->nullable()->default(0);
            $table->string('cc_api_key', 191)->nullable();
            $table->tinyInteger('balance_transfer')->default(0);
            $table->string('twilio_account_sid')->nullable();
            $table->string('twilio_auth_token')->nullable();
            $table->string('twilio_default_number')->nullable();
            $table->tinyInteger('twilio_status')->default(0);
            $table->string('nexmo_key')->nullable();
            $table->string('nexmo_secret')->nullable();
            $table->string('nexmo_default_number')->nullable();
            $table->tinyInteger('nexmo_status')->default(0);
            $table->tinyInteger('two_factor')->default(0);
            $table->tinyInteger('kyc')->default(0);
            $table->text('menu')->nullable();
            $table->mediumText('module_section')->nullable();
            $table->mediumText('user_module')->nullable();
            $table->tinyInteger('is_verify')->nullable()->default(0);
            $table->boolean('two_fa')->default(true);
            $table->double('other_bank_limit')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('generalsettings');
    }
}
