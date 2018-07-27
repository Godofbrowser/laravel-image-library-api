<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImageTagIntermediateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('image_tag', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('image_id');
            $table->unsignedInteger('tag_id');
            $table->timestamps();

            // Delete record if referenced image was deleted
			$table->foreign('image_id')->references('id')->on('images')->onDelete('cascade');

			// Delete record if referenced tag was deleted
			$table->foreign('tag_id')->references('id')->on('tags')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('image_tag');
    }
}
