<?php

use App\Models\Planning\Categorie;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('taches', function (Blueprint $table) {
            $table->foreignIdFor(Categorie::class)->constrained()->restrictOnDelete()->default(1);
        });
        Schema::table('archives', function (Blueprint $table) {
            $table->string('categorie_nom');
            $table->string('categorie_couleur');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('taches', function (Blueprint $table) {
            $table->dropForeign(['categorie_id']);
            $table->dropColumn('categorie_id');
        });
        Schema::table('archives', function (Blueprint $table) {
            $table->dropColumn('categorie_nom');
            $table->dropColumn('categorie_couleur');
        });
    }
};
