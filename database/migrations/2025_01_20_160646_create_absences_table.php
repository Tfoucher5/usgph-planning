<?php

use App\Classes\Commun\ExtendBlueprint;
use App\Enums\ValidationStatus;
use App\Models\Conge\Motif;
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

        $schema->create('absences', function (ExtendBlueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained();
            $table->foreignIdFor(Motif::class)->constrained();
            $table->date('date_debut');
            $table->date('date_fin');
            $table->string('status')->default(ValidationStatus::WAITING);
            $table->float('nb_of_work_days');
            $table->whoAndWhen();
        });

        Bouncer::allow('admin')->to('absence-confirm');
        Bouncer::allow('admin')->to('absence-refuse');
        Bouncer::allow('admin')->to('absence-retrieve');
        Bouncer::allow('salarie')->to('absence-delete');
        Bouncer::allow('salarie')->to('absence-create');
        Bouncer::allow('salarie')->to('absence-update');
        Bouncer::allow('salarie')->to('absence-retrieve');
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
        Schema::dropIfExists('absences');
        Schema::enableForeignKeyConstraints();
    }
};
