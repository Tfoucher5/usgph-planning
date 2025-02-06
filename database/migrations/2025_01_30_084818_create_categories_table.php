<?php

use App\Classes\Commun\ExtendBlueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $schema = DB::getSchemaBuilder();
        $schema->blueprintResolver(function ($table, $callback) {
            return new ExtendBlueprint($table, $callback);
        });

        $schema->create('categories', function (ExtendBlueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('couleur');
            $table->whoAndWhen();
        });

        Bouncer::allow('admin')->to('categorie-create');
        Bouncer::allow('admin')->to('categorie-update');
        Bouncer::allow('admin')->to('categorie-delete');
        Bouncer::allow('admin')->to('categorie-retrieve');
        Bouncer::Refresh();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('categories');
        Schema::enableForeignKeyConstraints();
    }
};
