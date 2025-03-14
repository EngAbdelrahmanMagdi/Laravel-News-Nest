<?php 

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->nullable()->constrained('authors')->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->foreignId('source_id')->nullable()->constrained('sources')->onDelete('set null');
            $table->string('title', 500);
            $table->text('summary')->nullable();
            $table->string('api_source');
            $table->string('url', 1024);
            $table->timestamp('published_at')->nullable();
            $table->string('image_url', 1024)->nullable();
            $table->timestamps();
        });
    }    

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropForeign(['author_id']);
            $table->dropColumn('author_id'); 
        });

        Schema::dropIfExists('articles');
    }
};
