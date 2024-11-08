<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('api_token')->nullable();
            $table->string('otp')->nullable();
            $table->string('type')->default('user');

            $table->string('referred_by')->nullable()->after('referral_code');

            $table->string('referral_code')->unique()->nullable();

            // $table->integer('hearts')->default(0);
            $table->boolean('facebook_connected')->default(false)->after('referral_code');
            $table->boolean('profile_complete')->default(false);
            $table->enum('status', ['active', 'in-active'])->default('active');
            $table->rememberToken();

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
        Schema::dropIfExists('users');
    }
}
