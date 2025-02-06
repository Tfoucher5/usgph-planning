<?php

namespace Tests\Feature\Models\Planning;

use App\Http\Controllers\Planning\TacheController;
use App\Http\Repositories\Planning\TacheRepository;
use App\Http\Services\Planning\PlanningService;
use App\Http\Services\Planning\TacheService;
use App\Models\Planning\Planning;
use App\Models\Planning\Tache;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class TacheTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * Appelle une méthode privée ou protégée dans un objet.
     *
     * @param  object  $object
     * @param  string  $method
     * @param  array  $parameters
     *
     * @return mixed
     */
    protected function invokeMethod($object, $method, array $parameters = [])
    {
        $reflection = new \ReflectionClass($object);
        $method = $reflection->getMethod($method);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    /**
     * @var string
     */
    private const MODEL = 'tache';

    public function test_fictif()
    {
        $this->assertTrue(true);
    }

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
        $tache = Tache::factory()
            ->create();

        $response = $this->get(route(self::MODEL . '.show', ['tache' => $tache->id]));

        $response->assertRedirect('login');
    }

    public function test_edit_need_login()
    {
        $tache = Tache::factory()
            ->create();

        $response = $this->get(route(self::MODEL . '.edit', ['tache' => $tache->id]));

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
        $tache = Tache::factory()->create([
            'heure_debut' => '09:00',
            'heure_fin' => '17:00',
        ]);
        $data = array_merge($tache->toArray());

        $response = $this->post(route(self::MODEL . '.store'), $data);
        $response->assertUnauthorized();
    }

    public function test_show_need_admin()
    {
        $this->setUser();
        $tache = Tache::factory()->create([
            'heure_debut' => '09:00',
            'heure_fin' => '17:00',
        ]);

        $response = $this->get(route(self::MODEL . '.show', ['tache' => $tache->id]));

        $response->assertUnauthorized();
    }

    public function test_edit_need_admin()
    {
        $this->setUser();
        $tache = Tache::factory()->create([
            'heure_debut' => '09:00',
            'heure_fin' => '17:00',
        ]);
        $response = $this->get(route(self::MODEL . '.edit', ['tache' => $tache->id]));

        $response->assertUnauthorized();
    }

    public function test_update_need_admin()
    {
        $this->setUser();
        $tache = Tache::factory()->create([
            'heure_debut' => '09:00',
            'heure_fin' => '17:00',
            'jour' => 3,
        ]);
        $data = array_merge($tache->toArray());
        $data['id'] = $tache->id;

        $response = $this->put(route(self::MODEL . '.update', ['tache' => $tache->id]), $data);

        $response->assertUnauthorized();
    }

    public function test_delete_need_admin()
    {
        $this->setUser();
        $tache = Tache::factory()->create([
            'heure_debut' => '09:00',
            'heure_fin' => '17:00',
        ]);

        $response = $this->delete(route(self::MODEL . '.destroy', ['tache' => $tache->id]));

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
        $this->setUser('admin');

        $tache = Tache::factory()->create();

        $response = $this->get(route('tache.model', ['tache' => $tache->id, 'ability' => 'create']));

        $response->assertStatus(200);
    }

    public function test_store()
    {
        $this->setUser('admin');

        $tache = Tache::factory()->create([
            'heure_debut' => '09:00',
            'heure_fin' => '17:00',
        ]);
        $data = array_merge($tache->toArray());

        $response = $this->post(route(self::MODEL . '.store'), $data);

        $response->assertSessionHas('ok');
    }

    public function test_edit()
    {
        $this->setUser('admin');

        $tache = Tache::factory()
            ->create();

        $response = $this->get(route(self::MODEL . '.edit', ['tache' => $tache->id]));

        $response->assertStatus(200);
    }

    public function test_update_with_permission()
    {
        $this->setUser('admin');

        $user = $this->setUser('salarie');

        $tache = Tache::factory()->create([
            'user_id' => $user->id,
            'heure_debut' => '09:00',
            'heure_fin' => '17:00',
        ]);
        $data = array_merge($tache->toArray(), [
            'nom' => 'Updated Task Name',
        ]);

        $response = $this->put(route('tache.update', ['tache' => $tache->id]), $data);

        $tacheUpdated = Tache::find($tache->id);

        $this->assertEquals('Updated Task Name', $tacheUpdated->nom);
        $this->assertNotNull($tacheUpdated->user_id_modification);
        $response->assertSessionHas('ok');
        $response->assertRedirect(route('tache.salarie', ['salarie_id' => $user->id, 'event_id' => $tache->id]));
    }

    public function test_update_without_permission()
    {
        $this->setUser('user');

        $tache = Tache::factory()->create([
            'heure_debut' => '09:00',
            'heure_fin' => '17:00',
            'jour' => 3,
        ]);
        $data = array_merge($tache->toArray(), [
            'nom' => 'Attempted Update',
        ]);

        $response = $this->put(route('tache.update', ['tache' => $tache->id]), $data);

        $response->assertStatus(401);
    }

    public function test_update_json_response()
    {
        $this->setUser('admin');

        $tache = Tache::factory()->create([
            'heure_debut' => '09:00',
            'heure_fin' => '17:00',
            'jour' => 3,
        ]);
        $data = array_merge($tache->toArray(), [
            'nom' => 'Updated Task Name',
            'description' => 'Updated Description',
        ]);

        $response = $this->put(route('tache.update', ['tache' => $tache->id]), $data, ['Accept' => 'application/json']);

        $response->assertJson([
            'success' => true,
            'message' => 'Mise à jour effectuée',
        ]);
    }

    public function test_show_tache_by_salarie_salarie_with_taches()
    {
        $admin = $this->setUser('admin');
        $user = $this->setUser(role: 'user');

        $tache = Tache::factory()->create(['user_id' => $user->id, 'heure_debut' => '09:00', 'heure_fin' => '12:00']);

        $response = $this->get(route('tache.salarie', ['salarie_id' => $user->id]));

        $response->assertViewIs('tache.index');
    }

    public function test_show_tache_by_salarie_non_admin()
    {
        $user = $this->setUser();

        $response = $this->get(route('tache.salarie', ['salarie_id' => 1]));

        $response->assertViewIs('tache.index');
        $response->assertSessionHas('error', 'Vous n\'avez pas les droits nécessaires pour cette action');
    }

    public function test_show_tache_by_salarie_salarie_without_taches()
    {
        $admin = $this->setUser('admin');
        $user = $this->setUser('user');

        $this->actingAs($admin);

        $tache = Tache::factory()->create(['user_id' => $user->id]);

        $response = $this->get(route('tache.salarie', ['tache' => $tache->id, 'salarie_id' => $user->id]));

        $response->assertViewIs('tache.index');
        $response->assertViewHas('tachesThisWeek');
        $response->assertViewHas('user', $user);
        $response->assertViewHas('isEmpty', false);
    }

    /** @test */
    public function it_allows_user_with_permission_to_validate_tache()
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
            'is_validated' => true,
            'duration' => 5,
        ];

        $response = $this->putJson(route('planning.validateTache', $planning), $data);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Tâche validée avec succès.',
            'hoursThisWeek' => 600,
        ]);
    }

    /** @test */
    public function it_returns_422_if_validation_fails()
    {
        $user = $this->setUser('salarie');
        $planning = Planning::factory()->create([
            'is_validated' => false,
        ]);

        $data = [
            'is_validated' => 'invalid_value',
            'duration' => -5,
        ];

        $response = $this->putJson(route('planning.validateTache', $planning), $data);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_returns_500_if_an_error_occurs_during_task_validation()
    {
        $user = $this->setUser('salarie');
        $planning = Planning::factory()->create([
            'is_validated' => false,
        ]);

        $this->mock(PlanningService::class, function ($mock) {
            $mock->shouldReceive('validateTache')->andThrow(new \Exception('An error occurred'));
        });

        $data = [
            'is_validated' => true,
            'duration' => 5,
        ];

        $response = $this->putJson(route('planning.validateTache', $planning), $data);

        $response->assertStatus(500);
        $response->assertJson([
            'message' => 'An error occurred',
            'exception' => 'Exception',
        ]);
    }

    public function testDestroyWithJsonResponse()
    {
        $user = $this->setUser('admin');
        $this->actingAs($user);

        $tache = Tache::factory()->create();

        $response = $this->deleteJson(route('tache.destroy', $tache));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Événement supprimé',
        ]);
    }

    public function testDestroyWithRedirectResponse()
    {
        $user = $this->setUser('admin');
        $this->actingAs($user);

        $tache = Tache::factory()->create();

        $response = $this->delete(route('tache.destroy', $tache));

        $response->assertStatus(302);
        $response->assertRedirect(route('tache.index'));
    }

    public function testDestroyUnauthorized()
    {
        $this->withoutExceptionHandling();
        $admin = $this->setUser('salarie');

        $tache = Tache::factory()->create();

        $response = $this->deleteJson(route('tache.destroy', $tache));

        $response->assertStatus(401);
        $response->assertJson([
            'message' => "Vous n'avez pas les droits nécessaires pour cette action"
        ]);
    }
}
