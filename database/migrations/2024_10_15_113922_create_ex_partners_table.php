<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExPartnersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ex_partners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('full_name');
            $table->enum('age_range', ['18-25', '26-35', '36-45', '46+']);
            $table->date('birthday');
            $table->enum('gender', ['male', 'female', 'non-binary']);
            $table->enum('relationship_duration', ['6 months - 1 year', '1 year - 3 years', '3 years - 5 years', '5 years - 10 years', '10+ years']);
            $table->string('college')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('profession')->nullable();
            $table->enum('status', ['active', 'in-active'])->default('active');
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
        Schema::dropIfExists('ex_partners');
    }
}
