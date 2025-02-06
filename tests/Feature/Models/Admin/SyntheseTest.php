<?php

namespace Tests\Feature\Models\Admin;

use App\Http\Repositories\Admin\SyntheseRepository;
use App\Http\Requests\Admin\FilterRequest;
use App\Http\Services\Admin\SyntheseService;
use App\Http\Services\Planning\PlanningService;
use App\Models\Conge\Absence;
use App\Models\Conge\Motif;
use App\Models\Planning\Planning;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Pagination\LengthAwarePaginator;

class SyntheseTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected $user;

    protected $admin;

    protected $service;

    protected $mockData;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->setUser('user');

        $this->admin = $this->setUser('admin');

        $this->service = $this->mock(SyntheseService::class);

        $this->mockData = [
            'S1' => [
                'annee' => 2024,
                'semaine' => 1,
                'jours' => [
                    1 => ['heures' => 8.0, 'ferie' => false, 'repos' => false],
                    2 => ['heures' => 8.0, 'ferie' => false, 'repos' => false],
                    3 => ['heures' => 8.0, 'ferie' => false, 'repos' => false],
                    4 => ['heures' => 8.0, 'ferie' => false, 'repos' => false],
                    5 => ['heures' => 8.0, 'ferie' => false, 'repos' => false],
                    6 => ['heures' => 0.0, 'ferie' => false, 'repos' => false],
                    0 => ['heures' => 0.0, 'ferie' => false, 'repos' => true],
                ],
                'total' => 40.0,
                'heures_supp' => 5.0,
            ],
        ];
    }

    /** @test */
    public function index_filters_work_weeks_for_last_three_months()
    {
        $currentDate = Carbon::now();

        $mockEmployees = [
            [
                'id' => 1,
                'email' => 'John.Doe@gmail.com',
                'name' => 'John Doe',
            ],
        ];

        // Structure basée sur le mockData du setUp
        $mockHours = [
            'S' . $currentDate->copy()->subMonths(4)->weekOfYear => [
                'annee' => $currentDate->copy()->subMonths(4)->year,
                'semaine' => $currentDate->copy()->subMonths(4)->weekOfYear,
                'jours' => [
                    1 => ['heures' => 8.0, 'ferie' => false, 'repos' => false],
                    2 => ['heures' => 8.0, 'ferie' => false, 'repos' => false],
                    3 => ['heures' => 8.0, 'ferie' => false, 'repos' => false],
                    4 => ['heures' => 8.0, 'ferie' => false, 'repos' => false],
                    5 => ['heures' => 8.0, 'ferie' => false, 'repos' => false],
                    6 => ['heures' => 0.0, 'ferie' => false, 'repos' => false],
                    0 => ['heures' => 0.0, 'ferie' => false, 'repos' => true],
                ],
                'total' => 40.0,
                'heures_supp' => 5.0,
            ],
            'S' . $currentDate->weekOfYear => [
                'annee' => $currentDate->year,
                'semaine' => $currentDate->weekOfYear,
                'jours' => [
                    1 => ['heures' => 7.0, 'ferie' => false, 'repos' => false],
                    2 => ['heures' => 7.0, 'ferie' => false, 'repos' => false],
                    3 => ['heures' => 7.0, 'ferie' => false, 'repos' => false],
                    4 => ['heures' => 7.0, 'ferie' => false, 'repos' => false],
                    5 => ['heures' => 7.0, 'ferie' => false, 'repos' => false],
                    6 => ['heures' => 0.0, 'ferie' => false, 'repos' => false],
                    0 => ['heures' => 0.0, 'ferie' => false, 'repos' => true],
                ],
                'total' => 35.0,
                'heures_supp' => 0.0,
            ],
        ];

        // Mock des appels de service
        $this->service->shouldReceive('getInfosUtilisateurs')
            ->once()
            ->andReturn($mockEmployees);

        $this->service->shouldReceive('getHeuresParSemaine')
            ->once()
            ->andReturn(collect($mockHours));

        // Act
        $response = $this->actingAs($this->admin)
            ->get(route('synthese.index'));

        // Assert
        $response->assertStatus(200);

        $employees = $response->viewData('employees');

        // Vérifie que nous avons un employé avec ses semaines de travail
        $this->assertNotEmpty($employees);
        $this->assertArrayHasKey('workWeeks', $employees[0]);

        // Vérifie qu'il n'y a qu'une seule semaine (la plus récente)
        $this->assertCount(1, $employees[0]['workWeeks']);

        // Vérifie que la semaine conservée est celle avec le total de 35 heures
        $workWeek = array_values($employees[0]['workWeeks'])[0];
        $this->assertEquals(35.0, $workWeek['total']);
        $this->assertEquals(0.0, $workWeek['heures_supp']);
    }

    /** @test */
    public function test_show_for_admin()
    {
        $this->service
            ->shouldReceive('getHeuresParSemaine')
            ->once()
            ->with([], $this->user->id, null)
            ->andReturn(collect($this->mockData));

        $this->service
            ->shouldReceive('getAnneesDisponibles')
            ->once()
            ->with($this->user->id)
            ->andReturn(collect(['2024-2025' => ['value' => '2025-08-31', 'label' => '2024-2025']]));

        $response = $this->actingAs($this->admin)
            ->get(route('synthese.show', $this->user->id));

        $response->assertStatus(200)
            ->assertViewIs('synthese.show')
            ->assertViewHas('semaines')
            ->assertViewHas('annees')
            ->assertViewHas('user');
    }

    /** @test */
    public function test_get_infos_utilisateur()
    {
        $expectedData = [
            'id' => $this->user->id,
            'name' => $this->user->identity,
            'email' => $this->user->email,
        ];

        // Create a real service instance with mocked dependencies
        $repository = $this->mock(SyntheseRepository::class);
        $planningService = $this->mock(PlanningService::class);
        $service = new SyntheseService($repository, $planningService);

        $result = $service->getInfosParUtilisateur($this->user->id);

        $this->assertEquals($expectedData, $result);
    }

    /** @test */
    public function test_get_heures_par_semaine_avec_filtres()
    {
        $this->actingAs($this->admin);

        $dateAnnee = Carbon::createFromDate(2024, 1, 1)->format('Y-m-d');

        $filtres = [
            'annee' => $dateAnnee,
            'semaine' => '1',
            'mois' => '1',
        ];

        $this->service
            ->shouldReceive('getHeuresParSemaine')
            ->once()
            ->with($filtres, $this->user->id, null)
            ->andReturn(collect($this->mockData));

        $this->service
            ->shouldReceive('getAnneesDisponibles')
            ->once()
            ->with($this->user->id)
            ->andReturn(collect(['2024-2025' => ['value' => '2025-08-31', 'label' => '2024-2025']]));

        $response = $this->actingAs($this->admin)
            ->get(route('synthese.show', [
                'user' => $this->user->id,
                'annee' => $dateAnnee,
                'semaine' => '1',
                'mois' => '1',
            ]));

        $response->assertStatus(200)
            ->assertViewHas('semaines', function ($semaines) {
                return $semaines->first()['semaine'] === 1
                    && $semaines->first()['annee'] === 2024
                    && $semaines->first()['total'] === 40.0;
            });
    }

    // /** @test */
    // public function test_export_csv()
    // {
    //     $this->service
    //         ->shouldReceive('getHeuresParSemaine')
    //         ->once()
    //         ->withArgs(function ($filters, $userId, $date) {
    //             return empty($filters) &&
    //                 $userId === $this->user->id &&
    //                 $date instanceof Carbon;
    //         })
    //         ->andReturn(collect($this->mockData));

    //     $response = $this->actingAs($this->admin)
    //         ->get(route('export.csv', $this->user->id));

    //     $response->assertStatus(200)
    //         ->assertHeader('Content-Type', 'text/csv; charset=UTF-8')
    //         ->assertHeader('Content-Disposition', 'attachment; filename=heures_travaillees_' . now()->format('Y_m_d') . '.csv');

    //     $content = $response->getContent();
    //     $this->assertStringContainsString('Année-Semaine,Lundi,Mardi,Mercredi,Jeudi,Vendredi,Samedi,Dimanche,Total,Heures supp', $content);

    // }

    /** @test */
    public function test_non_admin_cannot_access_synthese()
    {
        $response = $this->actingAs($this->user)
            ->get(route('synthese.index'));

        $response->assertRedirect();
        $this->assertEquals(
            'Vous n\'avez pas l\'autorisation d\'accéder à cette page.',
            session('erreur')
        );
    }

    // /** @test */
    // public function test_format_heures_par_semaine()
    // {
    //     $planning = (object) [
    //         'annee' => 2024,
    //         'mois' => 1,
    //         'jour' => 1,
    //         'jourSemaine' => 2, // Mardi
    //         'heures_travaillees' => 8.0
    //     ];

    //     $repository = $this->mock(SyntheseRepository::class);
    //     $planningService = $this->mock(PlanningService::class);
    //     $planningService->shouldReceive('getJoursFeries')
    //         ->andReturn(collect([]));

    //     $service = new SyntheseService($repository, $planningService);

    //     $result = $service->formatHeuresParSemaine(collect([$planning]));

    //     $this->assertIsArray($result->first()['jours']);
    //     $this->assertEquals(8.0, $result->first()['jours'][2]['heures']);
    //     $this->assertEquals(8.0, $result->first()['total']);
    // }

    /** @test */
    public function test_check_absences()
    {
        $motif = Motif::factory()->create(['nom' => 'Congé Annuel']);

        $absence = Absence::factory()->create([
            'user_id' => $this->user->id,
            'date_debut' => '2024-01-02',
            'date_fin' => '2024-01-02',
            'motif_id' => $motif->id,
        ]);

        $planningData = [
            'S1' => [
                'annee' => 2024,
                'semaine' => 1,
                'jours' => [
                    1 => ['heures' => 8.0, 'ferie' => false, 'repos' => false],
                    2 => ['heures' => 8.0, 'ferie' => false, 'repos' => false], // This should become an absence
                    3 => ['heures' => 8.0, 'ferie' => false, 'repos' => false],
                ],
                'total' => 24.0,
                'heures_supp' => 0.0,
            ],
        ];

        $repository = $this->mock(SyntheseRepository::class);
        $planningService = $this->mock(PlanningService::class);
        $service = new SyntheseService($repository, $planningService);

        $result = $service->checkAbsences($planningData, $this->user->id);

        $this->assertEquals('Congé Annuel', $result->first()['jours'][2]['absence']);
        $this->assertEquals(0.0, $result->first()['jours'][2]['heures']);
        $this->assertEquals(16.0, $result->first()['total']); // Total should be reduced by 8 hours
    }
    public function test_filters_are_correctly_applied()
    {
        $request = new FilterRequest();

        $request->merge([
            'annee' => '2025',
            'mois' => '01',
            'semaine' => '5',
            'date_debut' => '2025-01-29',
            'date_fin' => '2025-02-05',
        ]);

        $filters = $request->filters();

        $this->assertEquals([
            'annee' => '2025',
            'mois' => '01',
            'semaine' => '5',
            'date_debut' => '2025-01-29',
            'date_fin' => '2025-02-05',
        ], $filters);
    }

    public function test_show_graphique_year_as_admin()
    {
        $salarie = $this->setUser('salarie');
        $this->actingAs($this->admin);

        // Mock du service
        $serviceMock = \Mockery::mock(SyntheseService::class);
        $serviceMock->shouldReceive('getHeuresParSemaine')
            ->once()
            ->with([], $salarie->id, \Mockery::type(Carbon::class))
            ->andReturn(collect([
                ['semaine' => 1, 'heures' => 35, 'total' => 35],
                ['semaine' => 2, 'heures' => 37, 'total' => 37]
            ]));

        $this->app->instance(SyntheseService::class, $serviceMock);

        $response = $this->get(route('synthese.graphique', ['user' => $salarie->id]));

        $response->assertStatus(200);
        $response->assertViewIs('synthese.graphique');
        $response->assertViewHas('employee');

        $employee = $response->viewData('employee');
        $this->assertEquals($salarie->id, $employee['id']);
        $this->assertIsArray($employee['workWeeks']);
    }

    public function test_show_graphique_year_as_non_admin()
    {
        $salarie = $this->setUser('salarie');
        $this->actingAs($salarie);

        $response = $this->get(route('synthese.graphique', ['user' => $salarie->id]));

        $response->assertRedirect();
        $response->assertSessionHas('erreur', 'Vous n\'avez pas l\'autorisation d\'accéder à cette page.');
    }

    public function test_show_tache_validation_as_admin()
    {
        $salarie = $this->setUser('salarie');
        $this->actingAs($this->admin);

        $userAttributes = [
            'id' => $salarie->id,
            'first_name' => $salarie->first_name,
            'last_name' => $salarie->last_name,
            'email' => $salarie->email,
            'email_verified_at' => $salarie->email_verified_at,
            'created_at' => $salarie->created_at,
            'updated_at' => $salarie->updated_at
        ];

        $planning = Planning::factory()->create(['user_id' => $salarie->id]);
        $planning2 = Planning::factory()->create(['user_id' => $salarie->id]);
        Planning::factory()->create(['user_id' => $salarie->id]);
        Planning::factory()->create(['user_id' => $salarie->id]);
        Planning::factory()->create(['user_id' => $salarie->id]);

        $syntheseServiceMock = \Mockery::mock(SyntheseService::class);
        $syntheseServiceMock->shouldReceive('getTacheToValidate')
            ->once()
            ->withAnyArgs()
            ->andReturn(new LengthAwarePaginator(
                collect([$planning, $planning2]),
                5,
                10,
                1,
                ['path' => url('/')]
            ));

        $planningServiceMock = \Mockery::mock(PlanningService::class);
        $planningServiceMock->shouldReceive('getSalaries')
            ->once()
            ->andReturn(collect([1 => $salarie]));

        $this->app->instance(SyntheseService::class, $syntheseServiceMock);
        $this->app->instance(PlanningService::class, $planningServiceMock);

        $response = $this->get(route('synthese.tacheValidation', ['salarie_id' => $salarie->id]));

        $response->assertStatus(200);
        $response->assertViewIs('synthese.tacheValidation');
        $response->assertViewHasAll(['plannings', 'user', 'salaries']);
        $this->assertEquals($salarie->id, $response->viewData('user')->id);
        $this->assertCount(2, $response->viewData('plannings'));
    }


    public function test_show_tache_validation_as_non_admin()
    {
        $salarie = $this->setUser('salarie');
        $this->actingAs($salarie);

        $response = $this->get(route('synthese.tacheValidation', ['salarie_id' => $salarie->id]));

        $response->assertRedirect();
        $response->assertSessionHas('erreur', 'Vous n\'avez pas la permission d\'accéder à cette page.');
    }
}
