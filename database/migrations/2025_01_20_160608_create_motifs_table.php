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

        $schema->create('motifs', function (ExtendBlueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('cannot_delete')->default('0');
            $table->whoAndWhen();
        });

        Bouncer::allow('admin')->to('motif-create');
        Bouncer::allow('admin')->to('motif-update');
        Bouncer::allow('admin')->to('motif-delete');
        Bouncer::allow('admin')->to('motif-retrieve');
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
        Schema::dropIfExists('motifs');
        Schema::enableForeignKeyConstraints();
    }
};
