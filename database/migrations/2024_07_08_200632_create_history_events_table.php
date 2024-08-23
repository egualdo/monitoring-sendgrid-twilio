<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistoryEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('history_events', function (Blueprint $table) {
            $table->id();
            $table->string('sg_message_id');    //14c5d75ce93.dfd.64b469.filter0001.16648.5515E0B88.0
            $table->string('email');            //test@email.com
            $table->string('event');            //open,clicked...
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('history_events');
    }
}
