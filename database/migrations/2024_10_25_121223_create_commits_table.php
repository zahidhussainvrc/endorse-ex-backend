<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('commits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // ID of the user making the commit
            $table->foreignId('ex_partner_id')->constrained('ex_partners')->onDelete('cascade'); // ID of the ex-partner profile
            $table->text('message'); // The commit message
            $table->enum('status', ['approve', 'inapprove'])->default('inapprove'); // Status of the commit
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
        Schema::dropIfExists('commits');
    }
}
