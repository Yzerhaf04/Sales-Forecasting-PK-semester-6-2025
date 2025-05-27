<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{

    public function up()
    {
        if (!Schema::hasTable('sales_data')) {
            Schema::create('sales_data', function (Blueprint $table) {
                $table->id();
                $table->date('Date');
                $table->integer('Store');
                $table->integer('Dept');
                $table->decimal('Daily_Sales', 10, 2);
                $table->timestamp('updated_at')->nullable();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('sales_data');
    }
};
