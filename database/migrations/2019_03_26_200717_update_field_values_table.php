<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateFieldValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('field_values', function(Blueprint $table) {
            $table->string('field_id');
            $table->string('page_id');
            $table->text('value');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('field_values', function(Blueprint $table) {
            $table->dropColumn('field_id');
            $table->dropColumn('page_id');
            $table->dropColumn('value');
        });
    }
}
