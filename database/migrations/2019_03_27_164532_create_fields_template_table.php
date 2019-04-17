<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFieldsTemplateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fields_template', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->text('field_id');
            $table->text('template_id');
        });

        Schema::dropIfExists('template_fields');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fields_template');
    }
}
