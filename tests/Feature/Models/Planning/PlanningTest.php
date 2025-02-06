<?php

namespace Tests\Feature\Models\Planning;

use App\Http\Controllers\Planning\PlanningController;
use App\Http\Repositories\Planning\PlanningRepository;
use App\Http\Requests\Planning\TacheValidationRequest;
use App\Http\Services\Planning\PlanningService;
use App\Models\Admin\Lieu;
use App\Models\Planning\Planning;
use App\Models\Planning\Tache;
use App\Models\User;
use Auth;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Route;
use Tests\TestCase;

class PlanningTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * @var string
     */
    private const MODEL = 'planning';

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
        $planning = Planning::factory()
            ->create();

        $response = $this->get(route(self::MODEL . '.show', ['planning' => $planning->id]));

        $response->assertRedirect('login');
    }

    public function test_edit_need_login()
    {
        $planning = Planning::factory()
            ->create();

        $response = $this->get(route(self::MODEL . '.edit', ['planning' => $planning->id]));

        $response->assertRedirect('login');
    }

    public function test_index_need_admin()
    {
        $this->setUser();

        $response = $this->get(route(self::MODEL . '.index'));

        $response->assertSessionHas('erreur', 'Vous n\'avez pas l\'autorisation d\'accéder à cette page');
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
        $planning = Planning::factory()
            ->make();
        $data = array_merge($planning->toArray());

        $response = $this->post(route(self::MODEL . '.store'), $data);
        $response->assertUnauthorized();
    }

    public function test_show_need_admin()
    {
        $this->setUser();
        $planning = Planning::factory()
            ->create();

        $response = $this->get(route(self::MODEL . '.show', ['planning' => $planning->id]));

        $response->assertUnauthorized();
    }

    public function test_edit_need_admin()
    {
        $this->setUser();
        $planning = Planning::factory()
            ->create();

        $response = $this->get(route(self::MODEL . '.edit', ['planning' => $planning->id]));

        $response->assertUnauthorized();
    }

    public function test_update_need_admin()
    {
        $this->setUser();
        $planning = Planning::factory()->create([
        ]);

        $data = [
            'id' => $planning->id,
            'plannifier_le' => now()->format('Y-m-d'),
            'heure_debut' => '08:00',
            'heure_fin' => '12:00',
            'nom' => 'test nom',
        ];

        $response = $this->put(route(self::MODEL . '.update', ['planning' => $planning->id]), $data);

        $response->assertUnauthorized();
    }

    public function test_delete_need_admin()
    {
        $this->setUser('admin');

        $planning = Planning::factory()->create();

        $response = $this->delete(route(self::MODEL . '.destroy', ['planning' => $planning->id]));

        $response->assertJson([
            'success' => true,
            'message' => 'Événement supprimé',
        ]);
    }

    // public function test_undelete_need_admin()
    // {
    //     $this->setUser();
    //     $planning = Planning::factory()
    //         ->create();

    //     $response = $this->get(route(self::MODEL . '.undelete', ['planning' => $planning->id]));

    //     $response->assertUnauthorized();
    // }

    // public function test_json_need_admin()
    // {
    //     $this->setUser();

    //     $response = $this->get(route(self::MODEL . '.json'));

    //     $response->assertUnauthorized();
    // }

    public function test_index()
    {
        $this->setUser('admin');

        $response = $this->get(route(self::MODEL . '.index'));

        $response->assertStatus(200);
    }

    public function test_create()
    {
        $this->setUser('admin');

        $response = $this->get(route(self::MODEL . '.create'));

        $response->assertStatus(200);
    }

    public function test_salarie_can_store_own_planning()
    {
        $user = $this->setUser('salarie', 'planning-create');
        $planning = Planning::factory()->make([
            'plannifier_le' => now()->format('Y-m-d'),
            'user_id' => $user->id,
            'heure_debut' => '08:00',
            'heure_fin' => '12:00',
        ]);
        $data = array_merge($planning->toArray());

        $response = $this->post(route(self::MODEL . '.store'), $data);

        $response->assertStatus(302);
        // $this->assertNotNull($planning->user_id_creation);
        $response->assertSessionHas('ok');
    }

    public function test_store()
    {
        $this->setUser('admin');

        $planning = Planning::factory()
            ->make(
                ['plannifier_le' => now()->format('Y-m-d')]
            );
        $data = array_merge($planning->toArray());

        $response = $this->post(route(self::MODEL . '.store'), $data);

        $this->assertNotNull($planning->user_id_creation);
        $response->assertSessionHas('ok');
    }

    public function test_edit()
    {
        $this->setUser('admin');

        $planning = Planning::factory()
            ->create();

        $response = $this->get(route(self::MODEL . '.edit', ['planning' => $planning->id]));

        $response->assertStatus(200);
    }

    public function test_update()
    {
        $user = $this->setUser('admin');
        $this->assertNotNull(Auth::id(), 'L\'utilisateur n\'est pas authentifié.');

        $user = User::factory()->create();
        Planning::factory()->count(50)->create(['user_id' => $user->id]);
        $planning = Planning::factory()->create(
            [
                'user_id' => $user->id,
                'plannifier_le' => now()->format('Y-m-d'),
                'heure_debut' => '08:00',
                'heure_fin' => '12:00',
                'nom' => 'test nom',
            ]
        );

        $data = [
            'user_id' => $user->id,
            'id' => $planning->id,
            'plannifier_le' => now()->format('Y-m-d'),
            'heure_debut' => '09:00',
            'heure_fin' => '13:00',
            'nom' => 'test nom',
            'user_id_modification' => $user->id,
        ];

        $response = $this->put(route(self::MODEL . '.update', ['planning' => $planning->id]), $data);
        $planning = Planning::find($planning->id);

        $response->assertStatus(302);
        $this->assertNotNull($planning->user_id_modification);
    }

    public function test_salarie_can_update_own_planning()
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $user = $this->setUser('salarie', 'planning-update');

        $planningAfter = Planning::factory()->create([
            'user_id' => $user->id,
            'heure_debut' => '10:00',
            'heure_fin' => '12:00',
            'plannifier_le' => now()->format('Y-m-d'),
        ]);
        $planningAfterAfter = Planning::factory()->create([
            'user_id' => $user->id,
            'heure_debut' => '13:00',
            'heure_fin' => '16:00',
            'plannifier_le' => now()->format('Y-m-d'),
        ]);

        $data = [
            'user_id' => $user->id,
            'id' => $planningAfter->id,
            'plannifier_le' => now()->format('Y-m-d'),
            'heure_debut' => '08:00',
            'heure_fin' => '10:00',
            'nom' => 'test nom',
        ];

        $response = $this->withoutExceptionHandling()->put(route(self::MODEL . '.update', ['planning' => $planningAfter->id]), $data);
        $planning = Planning::find($planningAfter->id);

        $response->assertStatus(302);
        $this->assertNotNull($planning->user_id_modification);
        $this->assertDatabaseHas('plannings', [
            'id' => $planning->id,
            'heure_debut' => '08:00',
            'heure_fin' => '10:00',
        ]);
        $this->assertDatabaseHas('plannings', [
            'id' => $planningAfterAfter->id,
            'heure_debut' => '13:00',
            'heure_fin' => '16:00',
        ]);
    }

    public function test_admin_can_delete_any_planning()
    {
        $this->setUser('admin');

        $Planning = Planning::factory()
            ->create();

        $response = $this->delete(route(self::MODEL . '.destroy', ['planning' => $Planning->id]));

        $this->assertSoftDeleted(Planning::class);
        $response->assertSessionHas(['ok']);
    }

    public function test_salarie_can_delete_own_planning()
    {
        $user = $this->setUser('salarie', self::MODEL . '-delete');

        $Planning = Planning::factory()
            ->create(['user_id' => $user->id]);

        $response = $this->delete(route(self::MODEL . '.destroy', ['planning' => $Planning->id]));

        $this->assertSoftDeleted(Planning::class);
        $response->assertSessionHas(['ok']);
    }

    public function test_salarie_cannot_delete_any_planning()
    {
        $this->setUser('salarie', 'Planning-delete');

        $otheruser = User::factory()->create();
        $Planning = Planning::factory()->create(['user_id' => $otheruser->id]);

        $response = $this->delete(route(self::MODEL . '.destroy', $Planning));

        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'Vous n\'avez pas les droits nécessaires pour cette action',
        ]);
    }

    // public function test_undelete()
    // {
    //     $this->setUser('admin');

    //     $planning = Planning::factory()
    //         ->create();

    //     $response = $this->delete(route(self::MODEL . '.destroy', ['planning' => $planning->id]));
    //     $this->assertSoftDeleted(Planning::class);
    //     $response->assertSessionHas(['ok']);

    //     $response = $this->get(route(self::MODEL . '.undelete', ['planning' => $planning->id]));

    //     $this->assertNull($planning->user_id_suppression);
    //     $response->assertSessionHas(['ok']);
    // }

    // public function test_json()
    // {
    //     $this->setUser('admin');

    //     $response = $this->get(route(self::MODEL . '.json'));

    //     $response->assertJsonStructure();
    // }

    public function test_is_planning_validated()
    {
        $this->setUser('salarie');

        $planning = Planning::factory()->create([
            'is_validated' => true,
        ]);

        $response = $this->getJson(route('planning.isTacheValidated', ['planning' => $planning->id]));

        $response->assertStatus(200)
            ->assertJson([
                'is_validated' => true,
            ]);
    }

    public function test_is_planning_not_validated()
    {
        $this->setUser('salarie');

        $planning = Planning::factory()->create([
            'is_validated' => false,
        ]);

        $response = $this->getJson(route('planning.isTacheValidated', ['planning' => $planning->id]));

        $response->assertStatus(200)
            ->assertJson([
                'is_validated' => false,
            ]);
    }

    public function test_tache_relationship()
    {

        $tache = Tache::factory()->create();

        $planning = Planning::factory()->create(['tache_id' => $tache->id]);

        $this->assertInstanceOf(Tache::class, $planning->tache);
        $this->assertEquals($tache->id, $planning->tache->id);
    }

    public function test_user_relationship()
    {
        $user = User::factory()->create();

        $planning = Planning::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $planning->user);
        $this->assertEquals($user->id, $planning->user->id);
    }

    public function test_lieu_relationship()
    {
        $lieu = Lieu::factory()->create();

        $planning = Planning::factory()->create(['lieu_id' => $lieu->id]);

        $this->assertInstanceOf(Lieu::class, $planning->lieu);
        $this->assertEquals($lieu->id, $planning->lieu_id);
    }

    /** @test */
    public function admin_can_view_planning_for_salarie()
    {
        $admin = $this->setUser('admin');
        $salarie = $this->setUser('salarie');

        $this->actingAs($admin);

        $tachesThisWeek = Tache::factory(3)->create(['user_id' => $salarie->id]);

        $response = $this->get(route('planning.salarie', ['salarie_id' => $salarie->id]));

        $response->assertStatus(200);
        $response->assertViewHas('tachesList');
        $response->assertViewHas('salaries');
        $response->assertViewHas('currentUserId');
    }

    // /** @test */
    // public function non_admin_user_cannot_view_planning()
    // {
    //     $user = $this->setUser('salarie');

    //     $response = $this->get(route('planning.salarie', ['salarie_id' => $user->id]));

    //     $response->assertStatus(403);
    //     $response->assertJson(['error' => 'Non autorisé']);
    // }

    // /** @test */
    // public function return_404_if_user_not_found()
    // {
    //     $this->setUser('admin');

    //     $response = $this->get(route('planning.salarie', ['salarie_id' => 999]));

    //     $response->assertStatus(404);
    //     $response->assertJson(['error' => 'Utilisateur introuvable']);
    // }

    /** @test */
    public function check_empty_taches_for_user()
    {
        $admin = $this->setUser('admin');
        $salarie = $this->setUser('salarie');

        $this->actingAs($admin);

        $response = $this->get(route('planning.salarie', ['salarie_id' => $salarie->id]));

        $response->assertStatus(200);
        $response->assertViewHas('isEmpty', true);
    }

    /** @test */
    public function test_importer_planning_returns_error_if_planning_already_exists()
    {
        $user = $this->setUser('salarie');

        Carbon::setTestNow(Carbon::create(2025, 1, 14));
        $existingPlanning = Planning::factory([
            'user_id' => $user->id,
            'plannifier_le' => Carbon::now()->startOfWeek()->toDateString(),
            'nom' => 'Exemple Planning',
            'user_id_creation' => $user->id,
        ])->create();

        $planning = new Planning;

        $startDate = now()->subDay();
        $endDate = now();

        $planningRepository = new PlanningRepository($planning);

        $result = $planningRepository->importerPlanning($user, $startDate, $endDate);

        $this->assertEquals(['error' => 'Planning déjà importé pour cette semaine.'], $result->toArray());
    }

    public function test_importer_planning_creates_plannings_in_database()
    {
        $user = $this->setUser('salarie');

        $lieu = Lieu::factory([
            'user_id_creation' => $user->id,
            'nom' => 'un lieu comme ca',
        ])->create();

        $tache = Tache::factory([
            'user_id' => $user->id,
            'lieu_id' => $lieu->id,
            'nom' => 'Réunion de projet',
            'heure_debut' => '10:00',
            'heure_fin' => '12:00',
            'jour' => 3,
            'user_id_creation' => $user->id,
        ])->create();

        Auth::login($user);

        $planning = new Planning;

        $startDate = now()->subDay();
        $endDate = now();

        $planningRepository = new PlanningRepository($planning);

        $result = $planningRepository->importerPlanning($user, $startDate, $endDate);

        $this->assertDatabaseHas('plannings', [
            'user_id' => $user->id,
            'lieu_id' => $lieu->id,
            'heure_debut' => '10:00',
            'heure_fin' => '12:00',
        ]);
    }

    /** @test */
    public function test_get_taches_by_date_range_returns_tasks_in_date_range()
    {
        $user = $this->setUser('salarie');

        $tache1 = Tache::factory([
            'user_id' => $user->id,
            'nom' => 'Réunion de projet',
            'heure_debut' => '10:00',
            'heure_fin' => '12:00',
            'jour' => 3,
            'user_id_creation' => $user->id,
        ])->create();

        $tache2 = Tache::factory([
            'user_id' => $user->id,
            'nom' => 'Workshop',
            'heure_debut' => '14:00',
            'heure_fin' => '16:00',
            'jour' => 4,
            'user_id_creation' => $user->id,
        ])->create();

        $planning1 = Planning::factory([
            'nom' => $tache1->nom,
            'user_id' => $user->id,
            'tache_id' => $tache1->id,
            'plannifier_le' => Carbon::now()->startOfWeek()->addDays(2)->toDateString(),
            'heure_debut' => $tache1->heure_debut,
            'heure_fin' => $tache1->heure_fin,
            'user_id_creation' => $user->id,
            'is_validated' => 0,
        ])->create();

        $planning2 = Planning::factory([
            'nom' => $tache2->nom,
            'user_id' => $user->id,
            'tache_id' => $tache2->id,
            'plannifier_le' => Carbon::now()->startOfWeek()->addDays(3)->toDateString(),
            'heure_debut' => $tache2->heure_debut,
            'heure_fin' => $tache2->heure_fin,
            'user_id_creation' => $user->id,
            'is_validated' => 0,
        ])
            ->create();

        $planning = new Planning;

        $repository = new PlanningRepository($planning);

        $result = $repository->getTachesByDateRange(Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek());

        $this->assertCount(2, $result);
    }

    /** @test */
    public function test_map_taches_for_calendar_maps_tasks_for_calendar()
    {
        $user = $this->setUser('salarie');

        $lieu = Lieu::factory(['nom' => 'Salle de conférence', 'user_id_creation' => $user->id])->create();

        $tache1 = Tache::factory([
            'user_id' => $user->id,
            'nom' => 'Réunion de projet',
            'heure_debut' => '10:00',
            'heure_fin' => '12:00',
            'jour' => 3,
            'user_id_creation' => $user->id,
            'lieu_id' => $lieu->id,
        ])->create();

        $tache2 = Tache::factory([
            'user_id' => $user->id,
            'nom' => 'Formation interne',
            'heure_debut' => '14:00',
            'heure_fin' => '16:00',
            'jour' => 4,
            'user_id_creation' => $user->id,
            'lieu_id' => $lieu->id,
        ])->create();

        $planning1 = Planning::factory([
            'user_id' => $user->id,
            'tache_id' => $tache1->id,
            'plannifier_le' => Carbon::now()->startOfWeek()->addDays(2)->toDateString(),
            'heure_debut' => $tache1->heure_debut,
            'heure_fin' => $tache1->heure_fin,
            'user_id_creation' => $user->id,
        ])->create();

        $planning2 = Planning::factory([
            'user_id' => $user->id,
            'tache_id' => $tache2->id,
            'plannifier_le' => Carbon::now()->startOfWeek()->addDays(3)->toDateString(),
            'heure_debut' => $tache2->heure_debut,
            'heure_fin' => $tache2->heure_fin,
            'user_id_creation' => $user->id,
        ])->create();

        $plannings = collect([$planning1, $planning2]);

        $planning = new Planning;

        $repository = new PlanningRepository($planning);

        $result = $repository->mapTachesForCalendar($plannings);

        $this->assertCount(2, $result);
    }

    /** @test */
    public function test_get_heures_prevues_cette_semaine_calculates_total_hours_for_user_this_week()
    {
        $user = $this->setUser('salarie');

        $planning1 = Planning::factory([
            'user_id' => $user->id,
            'plannifier_le' => Carbon::now()->startOfWeek()->addDays(1)->toDateString(),
            'heure_debut' => '10:00',
            'heure_fin' => '12:00',
            'created_at' => Carbon::now()->startOfWeek()->addDays(1),
            'user_id_creation' => $user->id,
        ])->create();

        $planning2 = Planning::factory([
            'user_id' => $user->id,
            'plannifier_le' => Carbon::now()->startOfWeek()->addDays(1)->toDateString(),
            'heure_debut' => '14:00',
            'heure_fin' => '16:00',
            'created_at' => Carbon::now()->startOfWeek()->addDays(2),
            'user_id_creation' => $user->id,
        ])->create();

        $planning3 = Planning::factory([
            'user_id' => $user->id,
            'plannifier_le' => Carbon::now()->startOfWeek()->addDays(1)->toDateString(),
            'heure_debut' => '09:00',
            'heure_fin' => '11:00',
            'created_at' => Carbon::now()->startOfWeek()->addDays(3),
            'user_id_creation' => $user->id,
        ])->create();

        $planning = new Planning;

        $repository = new PlanningRepository($planning);
        $totalMinutes = $repository->getHeuresPrevuesCetteSemaine($user);

        $expectedMinutes = Carbon::parse('10:00')->diffInMinutes(Carbon::parse('12:00')) +
            Carbon::parse('14:00')->diffInMinutes(Carbon::parse('16:00')) +
            Carbon::parse('09:00')->diffInMinutes(Carbon::parse('11:00'));

        $this->assertEquals($expectedMinutes, $totalMinutes);
    }

    /** @test */
    public function it_validates_tache_successfully()
    {
        $user = $this->setUser('salarie');

        $planning = Planning::factory()->create([
            'user_id' => $user->id,
            'heure_debut' => '8:00',
            'heure_fin' => '13:00',
            'plannifier_le' => Carbon::now()->startOfWeek()->addDays(1)->toDateString(),
        ]);

        $planning2 = Planning::factory()->create([
            'user_id' => $user->id,
            'heure_debut' => '8:00',
            'heure_fin' => '13:00',
            'plannifier_le' => Carbon::now()->startOfWeek()->addDays(1)->toDateString(),
            'is_validated' => true,
        ]);

        $data = [
            'event_id' => $planning->id,
            'user_id' => $user->id,
            'duration' => 5,
            'is_validated' => true,
        ];

        $mock = $this->mock(\App\Http\Services\Planning\TacheService::class, function ($mock) {
            $mock->shouldReceive('validateTache')
                ->withArgs(function ($planning, $user, $duration, $isValidated) {
                    return $planning instanceof Planning &&
                        $user instanceof User &&
                        $isValidated === true;
                })
                ->andReturn([
                    'success' => true,
                    'message' => 'Tâche validée avec succès.',
                    'hoursThisWeek' => 600,
                ]);
        });

        $response = $this->put(route('planning.validateTache', $planning), $data);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Tâche validée avec succès.',
                'hoursThisWeek' => 600,
            ]);
    }

    /** @test */
    public function it_allows_user_with_permission_to_import_planning()
    {
        $user = $this->setUser('salarie');

        $events = [
            'event1' => ['user_id' => $user->id, 'name' => 'Event 1', 'date' => '2025-01-16', 'heure_debut' => '08:00', 'heure_fin' => '12:00'],
            'event2' => ['user_id' => $user->id, 'name' => 'Event 2', 'date' => '2025-01-17', 'heure_debut' => '08:00', 'heure_fin' => '12:00'],
        ];

        $startDate = now()->subDay();
        $endDate = now();

        $this->actingAs($user);

        $this->mock(PlanningService::class, function ($mock) use ($user, $events) {
            $mock->shouldReceive('importerPlanning')
                ->once()
                ->with(
                    \Mockery::on(function ($arg) use ($user) {
                        return $arg->id === $user->id;
                    }),
                    \Mockery::type(Carbon::class),
                    \Mockery::type(Carbon::class)
                )
                ->andReturn($events);
        });

        $response = $this->getJson(route('planning.importer'), [
            'start' => $startDate->toDateString(),
            'end' => $endDate->toDateString(),
        ]);

        $response->assertStatus(200);
        $response->assertJson($events);
    }

    /** @test */
    public function it_returns_403_if_user_does_not_have_permission()
    {
        $user = $this->setUser('admin');

        $response = $this->getJson(route('planning.importer'));

        $response->assertJson([
            'error' => 'Vous n\'avez pas les droits',
        ]);
    }

    /** @test */
    public function it_returns_error_if_importing_planning_fails()
    {
        $user = $this->setUser('salarie');

        $this->actingAs($user);

        $this->mock(PlanningService::class, function ($mock) {
            $mock->shouldReceive('importerPlanning')
                ->once()
                ->andReturn(['error' => 'Une erreur est survenue']);
        });

        $response = $this->getJson(route('planning.importer'), [
            'start' => now()->subDay()->toDateString(),
            'end' => now()->toDateString(),
        ]);

        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function it_indicates_button_visibility_based_on_tasks_presence()
    {
        $this->actingAs($this->setUser('salarie'));

        $startDate = now()->startOfWeek()->setTimezone('Europe/Paris');
        $endDate = now()->endOfWeek()->setTimezone('Europe/Paris');

        $planningService = \Mockery::mock(PlanningService::class);
        $this->app->instance(PlanningService::class, $planningService);

        // Test when there are no tasks
        $planningService->shouldReceive('getTachesByDateRange')
            ->once()
            ->withArgs(function ($start, $end) use ($startDate, $endDate) {
                return $start->equalTo($startDate) && $end->equalTo($endDate);
            })
            ->andReturn([]);

        $response = $this->getJson("/api/planning/events?start={$startDate->toDateString()}&end={$endDate->toDateString()}");

        $response->assertOk()
            ->assertJson(['data' => true]); // On enveloppe le booléen dans un tableau

        // Test when there are tasks
        $planningService->shouldReceive('getTachesByDateRange')
            ->once()
            ->withArgs(function ($start, $end) use ($startDate, $endDate) {
                return $start->equalTo($startDate) && $end->equalTo($endDate);
            })
            ->andReturn([['id' => 1, 'name' => 'Tâche 1']]);

        $response = $this->getJson("/api/planning/events?start={$startDate->toDateString()}&end={$endDate->toDateString()}");

        $response->assertOk()
            ->assertJson(['data' => false]);
    }

    public function testValidateTacheUnauthorized()
    {
        // Créer un utilisateur avec le rôle 'salarie' qui n'a pas les droits nécessaires
        $user = $this->setUser('admin');

        // Créer une instance de la requête
        $request = \Mockery::mock(TacheValidationRequest::class);
        $request->shouldReceive('validated')
            ->andReturn(['is_validated' => true]);

        // Créer une instance du planning
        $planning = Planning::factory()->create();

        // Simuler l'authentification de l'utilisateur avec actingAs
        $this->actingAs($user);

        // Créer un mock du service PlanningService
        $planningServiceMock = \Mockery::mock(PlanningService::class);
        $planningServiceMock->shouldReceive('can')
            ->with('planning-validate')
            ->andReturn(false); // L'utilisateur n'a pas la permission de valider la tâche

        // Injecter le service dans le contrôleur
        $controller = new PlanningController($planningServiceMock);

        // Appeler la méthode à tester
        $response = $controller->validateTache($request, $planning);

        // Vérifier que la réponse est une erreur 403
        $this->assertEquals(403, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $this->assertJsonStringEqualsJsonString(json_encode([
            'success' => false,
            'message' => "Vous n'avez pas les droits nécessaires pour cette action"
        ]), $response->getContent());
    }

    public function testValidateTacheValidationFailed()
    {
        // Créer un utilisateur avec le rôle 'admin' (qui a les droits pour valider)
        $user = $this->setUser('salarie');

        // Créer une instance de la requête
        $request = \Mockery::mock(TacheValidationRequest::class);
        $request->shouldReceive('validated')
            ->once()
            ->andReturn(['is_validated' => false]);

        // Créer une instance du planning
        $planning = Planning::factory()->create();

        // Simuler l'authentification de l'utilisateur avec actingAs
        $this->actingAs($user);

        // Créer un mock du service PlanningService
        $planningServiceMock = \Mockery::mock(PlanningService::class);
        $planningServiceMock->shouldReceive('can')
            ->with('tache-validate')
            ->andReturn(true); // L'utilisateur a la permission de valider la tâche

        // Simuler un échec de validation
        $planningServiceMock->shouldReceive('validateTache')
            ->with($planning, $user, false)
            ->andReturn([
                'success' => false,
                'message' => 'Impossible de valider la tâche',
                'hoursThisWeek' => 0
            ]);

        // Injecter le service dans le contrôleur
        $controller = new PlanningController($planningServiceMock);

        // Appeler la méthode à tester
        $response = $controller->validateTache($request, $planning);

        // Vérifier que la réponse est une erreur 422 (Validation failed)
        $this->assertEquals(422, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $this->assertJsonStringEqualsJsonString(json_encode([
            'success' => false,
            'message' => 'Impossible de valider la tâche'
        ]), $response->getContent());

    }
}
