<?php

use App\Classes\Commun\ExtendBlueprint;
use App\Models\Admin\Lieu;
use App\Models\Planning\Tache;
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

        $schema->create('plannings', function (ExtendBlueprint $table) {
            $table->id();
            $table->foreignIdFor(Tache::class)->nullable(true);
            $table->foreignIdFor(User::class)->constrained()->restrictOnDelete();
            $table->foreignIdFor(Lieu::class)->nullable(true);
            $table->string('nom');
            $table->date('plannifier_le');
            $table->whoAndWhen();
        });

        Bouncer::allow('admin')->to('planning-create');
        Bouncer::allow('admin')->to('planning-update');
        Bouncer::allow('admin')->to('planning-delete');
        Bouncer::allow('admin')->to('planning-retrieve');
        Bouncer::allow('admin')->to('planning-edit');
        Bouncer::allow('admin')->to('planning-isValidated');
        Bouncer::allow('admin')->to('planning-importer');
        Bouncer::allow('salarie')->to('planning-retrieve');
        Bouncer::allow('salarie')->to('planning-create');
        Bouncer::allow('salarie')->to('planning-validate');
        Bouncer::allow('salarie')->to('planning-isValidated');
        Bouncer::allow('salarie')->to('planning-update');
        Bouncer::allow('salarie')->to('planning-delete');
        Bouncer::allow('salarie')->to('planning-edit');
        Bouncer::allow('salarie')->to('planning-importer');

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
        Schema::dropIfExists('plannings');
        Schema::enableForeignKeyConstraints();
    }
};
