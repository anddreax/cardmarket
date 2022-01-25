<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AssignedCards extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assigned_cards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_card');
            $table->foreign('id_card')->references('id')->on('cards');
            $table->unsignedBigInteger('id_collection');
            $table->foreign('id_collection')->references('id')->on('collections');
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
        //
    }
}
