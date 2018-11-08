<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('books', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->string('subtitle');
            $table->string('origin_title');
            $table->string('author');
            $table->string('translator');
            $table->string('image');
            $table->string('images');
            $table->string('publisher');
            $table->date('pubdate');
            $table->string('tags');
            $table->string('rating');
            $table->string('data');
            $table->string('keyword');
            $table->integer('pages');
            $table->string('isbn10');
            $table->string('isbn13');
            $table->string('binding');
            $table->decimal('price', 5,2);
            $table->string('author_intro');
            $table->string('series');
            $table->integer('series_id');
            $table->string('language');
            $table->string('subject');
            $table->string('age');
            $table->string('source');
            $table->string('source_hash');
            $table->text('summary');
            $table->text('catalog');
            $table->index('isbn10');
            $table->index('isbn13');
            $table->index('source_hash');
            $table->index('title');
            $table->softDeletes();
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
        Schema::dropIfExists('books');
    }
}
