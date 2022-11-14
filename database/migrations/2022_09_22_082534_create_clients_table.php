<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->text('client_name');
            $table->text('org_id');
            $table->text('org_type');
            $table->text('sid')->nullable();
            $table->text('token')->nullable();
            $table->text('oauth_refresh_token');
            $table->text('allow_security_flag');
            $table->text('allow_AI_flag')->nullable();
            $table->text('client_id');
            $table->text('client_secret');
            $table->text('name_space_sf');
            $table->text('client_email')->nullable();
            $table->text('is_allow_email')->nullable();
            $table->text('is_email_503_allow')->nullable();
            $table->text('is_allow_short_url')->nullable();
            $table->text('short_url_access_token')->nullable();
            $table->text('short_url_created_at');
            $table->text('short_url_updated_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clents');
    }
};
