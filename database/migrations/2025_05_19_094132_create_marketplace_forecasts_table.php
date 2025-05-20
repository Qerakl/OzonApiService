<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMarketplaceForecastsTable extends Migration
{
    public function up()
    {
        Schema::create('marketplace_forecasts', function (Blueprint $table) {
            $table->id();

            // Внешний ключ на площадку
            $table->foreignId('marketplace_api_key_id')->nullable()->constrained()->cascadeOnDelete();

            $table->string('article');
            $table->string('name');
            $table->integer('current_stock')->default(0);
            $table->integer('forecast')->default(0);
            $table->text('recommendations')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('marketplace_forecasts');
    }
}
