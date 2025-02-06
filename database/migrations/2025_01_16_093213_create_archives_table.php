<?php

use App\Classes\Commun\ExtendBlueprint;
use App\Models\Planning\Planning;
use App\Models\User;
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

        $schema->create('archives', function (ExtendBlueprint $table) {
            $table->id();
            $table->foreignIdFor(Planning::class)->constrained();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('nom');
            $table->string('lieu');
            $table->date('plannifier_le');
            $table->time('heure_debut');
            $table->time('heure_fin');
            $table->string('duree_tache');
            $table->whoAndWhen();
        });

        Bouncer::allow('admin')->to('archive-create');
        Bouncer::allow('admin')->to('archive-update');
        Bouncer::allow('admin')->to('archive-delete');
        Bouncer::allow('admin')->to('archive-retrieve');
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
        Schema::dropIfExists('archives');
        Schema::enableForeignKeyConstraints();
    }
};
