<?php

namespace Tests\Feature\Models\Conge;

use App\Enums\ValidationStatus;
use App\Models\Conge\Absence;
use App\Models\Conge\Motif;
use App\Models\Planning\Planning;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AbsenceTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * @var string
     */
    private const MODEL = 'absence';

    public function test_index_need_login()
    {
        $response = $this->get(route(self::MODEL . '.index'));

        $response->assertRedirect('login');
    }

    public function test_create_need_login()
    {
        $response = $this->get(route(self::MODEL . '.create'));

        $response->assertRedirect('login');
    }

    public function test_show_need_login()
    {
        $absence = Absence::factory()
            ->create();

        $response = $this->get(route(self::MODEL . '.show', ['absence' => $absence->id]));

        $response->assertRedirect('login');
    }

    public function test_edit_need_login()
    {
        $absence = Absence::factory()
            ->create();

        $response = $this->get(route(self::MODEL . '.edit', ['absence' => $absence->id]));

        $response->assertRedirect('login');
    }

    public function test_index_need_admin()
    {
        $this->setUser();

        $response = $this->get(route(self::MODEL . '.index'));

        $response->assertUnauthorized();
    }

    public function test_create_need_admin()
    {
        $this->setUser();

        $response = $this->get(route(self::MODEL . '.create'));

        $response->assertUnauthorized();
    }

    public function test_store_need_admin()
    {
        $this->setUser();
        $absence = Absence::factory()
            ->make();
        $data = array_merge($absence->toArray());

        $response = $this->post(route(self::MODEL . '.store'), $data);
        $response->assertUnauthorized();
    }

    public function test_show_need_admin()
    {
        $this->setUser();
        $absence = Absence::factory()
            ->create();

        $response = $this->get(route(self::MODEL . '.show', ['absence' => $absence->id]));

        $response->assertUnauthorized();
    }

    public function test_edit_need_admin()
    {
        $this->setUser();
        $absence = Absence::factory()
            ->create();

        $response = $this->get(route(self::MODEL . '.edit', ['absence' => $absence->id]));

        $response->assertUnauthorized();
    }

    public function test_update_need_admin()
    {
        $this->setUser();
        $absence = Absence::factory()
            ->create();
        $data = array_merge($absence->toArray());
        $data['id'] = $absence->id;

        $response = $this->put(route(self::MODEL . '.update', ['absence' => $absence->id]), $data);

        $response->assertUnauthorized();
    }

    public function test_delete_need_admin()
    {
        $this->setUser();
        $absence = Absence::factory()
            ->create();

        $response = $this->delete(route(self::MODEL . '.destroy', ['absence' => $absence->id]));

        $response->assertUnauthorized();
    }

    public function test_undelete_need_admin()
    {
        $this->setUser();
        $absence = Absence::factory()
            ->create();

        $response = $this->get(route(self::MODEL . '.undelete', ['absence_id' => $absence->id]));

        $response->assertUnauthorized();
    }

    public function test_json_need_admin()
    {
        $this->setUser();

        $response = $this->get(route(self::MODEL . '.json'));

        $response->assertUnauthorized();
    }

    public function test_index()
    {
        $this->setUser('admin');

        $response = $this->get(route(self::MODEL . '.index'));

        $response->assertStatus(200);
    }

    public function test_create()
    {
        $this->setUser('salarie');

        $response = $this->get(route(self::MODEL . '.create'));

        $response->assertStatus(200);
    }

    public function test_store()
    {
        $user = $this->setUser('salarie');
        Planning::factory()->count(50)->create(['user_id' => $user->id, 'heure_debut' => '04:00', 'heure_fin' => '19:00']);
        $absence = Absence::factory()
            ->make([
                'date_debut' => '2025-02-02',
                'date_fin' => '2025-02-02'
            ]);
        $data = array_merge($absence->toArray(), ['user_id' => $user->id]);

        $response = $this->post(route(self::MODEL . '.store'), $data);

        $response->assertSessionHas('ok');
    }

    public function test_store_conge_paye()
    {
        $user = $this->setUser('salarie');
        $motif = Motif::factory()->create(['id' => 1]);
        Planning::factory()->count(50)->create(['user_id' => $user->id, 'heure_debut' => '04:00', 'heure_fin' => '19:00']);
        $absence = Absence::factory()
            ->make([
                'motif_id' => $motif->id,
                'date_debut' => '2025-02-02',
                'date_fin' => '2025-02-02',
            ]);
        $data = array_merge($absence->toArray(), ['user_id' => $user->id]);

        $response = $this->post(route(self::MODEL . '.store'), $data);

        $response->assertSessionHas('ok');
    }

    public function test_edit()
    {
        $this->setUser('salarie');

        $absence = Absence::factory()
            ->create();

        $response = $this->get(route(self::MODEL . '.edit', ['absence' => $absence->id]));

        $response->assertStatus(200);
    }

    public function test_update()
    {
        $user = $this->setUser('salarie');
        $planning = Planning::factory()->count(50)->create(['user_id' => $user->id, 'heure_debut' => '04:45', 'heure_fin' => '19:00']);

        $absence = Absence::factory()
            ->create([
                'user_id' => $user->id,
                'date_debut' => '2025-02-02',
                'date_fin' => '2025-02-02'
            ]);
        $data = array_merge($absence->toArray(), ['user_id' => $user->id]);

        $response = $this->put(route(self::MODEL . '.update', ['absence' => $absence->id]), $data);
        $absence = Absence::find($absence->id);

        $this->assertNotNull($absence->user_id_modification);
        $response->assertSessionHas('ok');
    }

    public function test_update_conge_paye()
    {
        $user = $this->setUser('salarie');
        Planning::factory()->count(50)->create(['user_id' => $user->id, 'heure_debut' => '04:45', 'heure_fin' => '19:00']);
        $motif = Motif::factory()->create(['id' => 1]);
        $absence = Absence::factory()
            ->create([
                'motif_id' => $motif->id,
                'user_id' => $user->id,
                'date_debut' => '2025-02-02',
                'date_fin' => '2025-02-02',
            ]);
        $data = array_merge($absence->toArray(), ['user_id' => $user->id]);

        $response = $this->put(route(self::MODEL . '.update', ['absence' => $absence->id]), $data);
        $absence = Absence::find($absence->id);

        $this->assertNotNull($absence->user_id_modification);
        $response->assertSessionHas('ok');
    }

    public function test_delete()
    {
        $user = $this->setUser('salarie');

        $absence = Absence::factory()
            ->create(['user_id' => $user->id]);

        $response = $this->delete(route(self::MODEL . '.destroy', ['absence' => $absence->id]));

        $this->assertSoftDeleted(Absence::class);
        $response->assertSessionHas(['ok']);

    }

    public function test_undelete()
    {
        $user = $this->setUser('salarie');

        $absence = Absence::factory()
            ->create(['user_id' => $user->id]);

        $response = $this->delete(route(self::MODEL . '.destroy', ['absence' => $absence->id]));
        $this->assertSoftDeleted(Absence::class);
        $response->assertSessionHas(['ok']);

        $response = $this->get(route(self::MODEL . '.undelete', ['absence_id' => $absence->id]));
        $response->assertSessionHas(['ok']);

        $this->assertNull($absence->user_id_suppression);

    }

    public function test_json()
    {
        $this->setUser('admin');

        $response = $this->get(route(self::MODEL . '.json'));

        $response->assertJsonStructure();
    }

    public function test_confirm_need_admin()
    {
        $this->setUser();
        $absence = Absence::factory()->create();

        $response = $this->get(route(self::MODEL . '.confirm', ['absence' => $absence->id]));

        $response->assertUnauthorized();

    }

    public function test_confirm()
    {
        $this->setUser('admin');

        $absence = Absence::factory()->create();

        $response = $this->get(route(self::MODEL . '.confirm', ['absence' => $absence->id]));

        $response->assertSessionHas(['ok']);
        $this->AssertDatabaseHas('absences', [
            'id' => $absence->id,
            'status' => ValidationStatus::VALIDATED,
        ]);
    }

    public function test_refuse_need_admin()
    {
        $this->setUser();
        $absence = Absence::factory()->create();

        $response = $this->get(route(self::MODEL . '.refuse', ['absence' => $absence->id]));

        $response->assertUnauthorized();

    }

    public function test_refuse()
    {
        $this->setUser('admin');

        $absence = Absence::factory()->create();

        $response = $this->get(route(self::MODEL . '.refuse', ['absence' => $absence->id]));

        $response->assertSessionHas(['ok']);
        $this->AssertDatabaseHas('absences', [
            'id' => $absence->id,
            'status' => ValidationStatus::REFUSED,
        ]);
    }

    public function test_store_throws_exception_not_enough_repos()
    {
        $this->setUser('salarie');

        $absence = Absence::factory()->make([
            'date_debut' => '2025-02-02',
            'date_fin' => '2025-03-15',
        ]);
        $data = array_merge($absence->toArray());

        $response = $this->post(route(self::MODEL . '.store'), $data);

        $response->assertRedirect(route('absence.create'))
            ->assertSessionHas('error', 'Vous n\'avez pas assez de droit pour poser ce congé');

        $this->assertDatabaseCount('absences', 0);

        $this->assertDatabaseCount('absences', 0);

    }

    public function test_store_throws_exception_not_enough_conge()
    {
        $this->setUser('salarie');
        $motif = Motif::factory()->create();
        $absence = Absence::factory()->make([
            'motif_id' => $motif->id,
            'date_debut' => '2025-02-02',
            'date_fin' => '2025-03-15',
        ]);
        $data = array_merge($absence->toArray());

        $response = $this->post(route(self::MODEL . '.store'), $data);

        $response->assertRedirect(route('absence.create'))
            ->assertSessionHas('error', 'Vous n\'avez pas assez de droit pour poser ce congé');

        $this->assertDatabaseCount('absences', 0);

        $this->assertDatabaseCount('absences', 0);

    }

    public function test_update_throws_exception_not_enough_conge()
    {
        $user = $this->setUser('salarie');
        $motif = Motif::factory()->create();
        $absence = Absence::factory()->create([
            'user_id' => $user->id,
            'motif_id' => $motif->id,
            'date_debut' => '2025-02-02',
            'date_fin' => '2025-03-15',
        ]);
        $data = ['user_id' => $user->id, 'motif_id' => $absence->motif_id, 'date_debut' => '2025-03-02', 'date_fin' => '2025-03-25'];

        $response = $this->put(route(self::MODEL . '.update', ['absence' => $absence->id]), $data);

        $response->assertRedirect(route('absence.update', ['absence' => $absence->id]))
            ->assertSessionHas('error', 'Vous n\'avez pas assez de droit pour poser ce congé');

        $this->assertDatabasehas('absences', ['nb_of_work_days' => $absence->nb_of_work_days]);

    }

    public function test_updates_throws_exception_not_enough_repos()
    {
        $user = $this->setUser('salarie');

        $absence = Absence::factory()->create([
            'user_id' => $user->id,
            'date_debut' => '2025-02-02',
            'date_fin' => '2025-03-15',
        ]);
        $data = ['user_id' => $user->id, 'motif_id' => $absence->motif_id, 'date_debut' => '2025-03-02', 'date_fin' => '2025-03-25'];

        $response = $this->put(route(self::MODEL . '.update', ['absence' => $absence->id]), $data);

        $response->assertRedirect(route('absence.update', ['absence' => $absence->id]))
            ->assertSessionHas('error', 'Vous n\'avez pas assez de droit pour poser ce congé');

        $this->assertDatabasehas('absences', ['nb_of_work_days' => $absence->nb_of_work_days]);

    }

    public function test_show_absence_by_salarie_as_admin()
    {
        $admin = $this->setUser('admin');
        $salarie = $this->setUser('salarie');
        $this->actingAs($admin);

        Absence::factory()->count(5)->create([
            'user_id' => $salarie->id
        ]);

        $response = $this->get(route('absence.index', $salarie->id));

        $response->assertStatus(200);
        $response->assertViewIs('absence.index');

        $absences = $response->viewData('absences');
        foreach ($absences as $absence) {
            $this->assertEquals($salarie->id, $absence->user_id);
        }
    }

    public function test_get_tableau_data_as_admin()
    {
        $admin = $this->setUser('admin');

        $salarie = $this->setUser('salarie');

        $this->actingAs($admin);

        // Créer des absences
        $absence = Absence::factory()->create([
            'user_id' => $salarie->id,
            'date_debut' => '2024-01-01',
            'date_fin' => '2024-01-05',
            'status' => 'validée'
        ]);

        $response = $this->get(route('absence.tableau', [
            'salarie_id' => $salarie->id,
            'annee' => '2024-01-01'
        ]));

        $response->assertStatus(200);
        $response->assertViewIs('absence.tableau');
        $response->assertViewHasAll([
            'tableauData',
            'joursParMotif',
            'annees',
            'user',
            'annee',
            'salaries',
            'nbConges'
        ]);
    }

    public function test_get_tableau_data_as_non_admin()
    {
        $salarie = $this->setUser('salarie');

        $this->actingAs($salarie);

        $response = $this->get(route('absence.tableau', [
            'salarie_id' => $salarie->id,
            'annee' => '2024-01-01'
        ]));

        $response->assertRedirect();
        $response->assertSessionHas('erreur', 'Vous n\'avez pas la permission d\'accéder à cette page.');
    }

    public function test_get_tableau_data_without_year()
    {
        $admin = $this->setUser('admin');

        $salarie = $this->setUser('salarie');

        $this->actingAs($admin);

        $response = $this->get(route('absence.tableau', [
            'salarie_id' => $salarie->id
        ]));

        $response->assertStatus(200);
        $response->assertViewIs('absence.tableau');
    }

    /** @test */
    public function it_shows_deleted_absences_if_user_has_permission()
    {
        $absence = Absence::factory()->trashed()->create();

        $this->actingAs($this->setUser('salarie'));

        $response = $this->get(route('absence.corbeille'));

        $response->assertStatus(200);
        $response->assertViewHas('deletedAbsences', function ($viewDeletedAbsences) use ($absence) {
            return $viewDeletedAbsences->contains($absence);
        });
    }

    /** @test */
    public function it_aborts_401_if_user_does_not_have_permission()
    {
        $this->actingAs($this->setUser());

        $response = $this->get(route('absence.corbeille'));

        $response->assertStatus(401);
    }
}
