<?php

use App\Classes\Commun\ExtendBlueprint;
use App\Models\Admin\Lieu;
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

        $schema->create('taches', function (ExtendBlueprint $table) {
            $table->id();
            $table->string('nom')->default('default_name');
            $table->foreignIdFor(Lieu::class)->nullable()->constrained()->restrictOnDelete();
            $table->foreignIdFor(User::class)->nullable()->constrained()->restrictOnDelete();
            $table->time('heure_debut')->nullable();
            $table->time('heure_fin')->nullable();
            $table->integer('jour')->nullable()->comment('Jour de la semaine : 1 pour lundi, 7 pour dimanche');
            $table->whoAndWhen();
        });

        Bouncer::allow('admin')->to('tache-create');
        Bouncer::allow('admin')->to('tache-update');
        Bouncer::allow('admin')->to('tache-delete');
        Bouncer::allow('admin')->to('tache-retrieve');
        Bouncer::allow('admin')->to('tache-edit');
        Bouncer::allow('salarie')->to('tache-create');
        Bouncer::allow('salarie')->to('tache-update');
        Bouncer::allow('salarie')->to('tache-delete');
        Bouncer::allow('salarie')->to('tache-retrieve');
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
        Schema::dropIfExists('taches');
        Schema::enableForeignKeyConstraints();
    }
};
