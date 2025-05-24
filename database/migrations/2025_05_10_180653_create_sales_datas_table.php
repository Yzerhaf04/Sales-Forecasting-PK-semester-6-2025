<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sales_data', function (Blueprint $table) {

            $table->date('date');
            $table->integer('store');
            $table->integer('dept');
            $table->decimal('weekly_sales', 12, 2);
            $table->boolean('isholiday_x');
            $table->decimal('temperature', 5, 2);
            $table->decimal('fuel_price', 5, 2);

            // Kolom markdown
            $table->decimal('markdown1', 10, 2)->nullable();
            $table->decimal('markdown2', 10, 2)->nullable();
            $table->decimal('markdown3', 10, 2)->nullable();
            $table->decimal('markdown4', 10, 2)->nullable();
            $table->decimal('markdown5', 10, 2)->nullable();

            $table->decimal('cpi', 10, 4);
            $table->decimal('unemployment', 5, 2);
            $table->string('type', 1);
            $table->decimal('size', 10, 1);

            // Tambahkan composite index jika diperlukan
            $table->index(['date', 'store', 'dept']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('sales_data');
    }
};
