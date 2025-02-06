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

        $schema->create('lieux', function (ExtendBlueprint $table) {
            $table->id();
            $table->string('nom');
            $table->whoAndWhen();
        });

        Bouncer::allow('admin')->to('lieu-create');
        Bouncer::allow('admin')->to('lieu-update');
        Bouncer::allow('admin')->to('lieu-delete');
        Bouncer::allow('admin')->to('lieu-retrieve');
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
        Schema::dropIfExists('lieux');
        Schema::enableForeignKeyConstraints();
    }
};
