<?php

namespace Tests\Feature\Models\Admin;

use App\Models\Admin\Archive;
use App\Models\Admin\Lieu;
use App\Models\Planning\Planning;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;

class ArchiveTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * @var string
     */
    private const MODEL = 'archive';

    public function test_relations()
    {
        $user = User::factory()->create();

        $planning = Planning::factory()->create([
            'user_id' => $user->id,
            'is_validated' => 1,
        ]);

        $archive = Archive::factory()->create([
            'user_id' => $user->id,
            'planning_id' => $planning->id,
        ]);

        $this->assertInstanceOf(User::class, $archive->user);
        $this->assertEquals($user->id, $archive->user->id);

        $this->assertInstanceOf(Planning::class, $archive->planning);
        $this->assertEquals($planning->id, $archive->planning->id);
    }

    // public function test_planning_relation()
    // {
    //     $planning = Planning::factory()->create(['is_validated' => 1]);

    //     $archive = Archive::factory()->create(['planning_id' => $planning->id]);

    //     $this->assertInstanceOf(Planning::class, $archive->planning);

    //     $this->assertEquals($planning->id, $archive->planning->id);
    // }

    public function test_unique_planning_id_in_archive()
    {
        Planning::factory()->count(10)->create(['is_validated' => 1]);

        foreach (range(1, 5) as $i) {
            Archive::factory()->create();
        }

        $planningIds = Archive::pluck('planning_id');
        $this->assertCount($planningIds->count(), $planningIds->unique());
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
        $planning = Planning::factory()->count(10)->create(['is_validated' => 1]);
        $archive = Archive::factory()
            ->create();

        $response = $this->get(route(self::MODEL . '.show', ['archive' => $archive->id]));

        $response->assertRedirect('login');
    }

    public function test_edit_need_login()
    {
        $planning = Planning::factory()->count(10)->create(['is_validated' => 1]);

        $archive = Archive::factory()
            ->create();

        $response = $this->get(route(self::MODEL . '.edit', ['archive' => $archive->id]));

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

    public function test_show_need_admin()
    {
        $this->setUser();
        $planning = Planning::factory()->count(10)->create(['is_validated' => 1]);

        $archive = Archive::factory()
            ->create();

        $response = $this->get(route(self::MODEL . '.show', ['archive' => $archive->id]));

        $response->assertUnauthorized();
    }

    public function test_edit_need_admin()
    {
        $this->setUser();
        $planning = Planning::factory()->count(10)->create(['is_validated' => 1]);

        $archive = Archive::factory()
            ->create();

        $response = $this->get(route(self::MODEL . '.edit', ['archive' => $archive->id]));

        $response->assertUnauthorized();
    }

    public function test_archivate_need_admin()
    {
        $this->setUser();
        $planning = Planning::factory()->create(['is_validated' => 1]);
        // $archive = Archive::factory()
        //     ->create([
        //         'planning_id' => $planning->id,
        //     ]);
        $data = $planning->toArray();
        $data['planning_id'] = $data['id'];

        $response = $this->post(route(self::MODEL . '.archivate', ['planning_id' => $planning->id]), $data);

        $response->assertUnauthorized();
    }

    public function test_delete_need_admin()
    {
        $this->setUser('salarie');
        $planning = Planning::factory()->create(['is_validated' => 1]);

        $archive = Archive::factory()
            ->create();

        $response = $this->delete(route(self::MODEL . '.destroy', ['archive' => $archive->id]));

        $response->assertUnauthorized();
    }

    public function test_undelete_need_admin()
    {
        $this->setUser();
        $planning = Planning::factory()->create(['is_validated' => 1]);

        $archive = Archive::factory()
            ->create();

        $response = $this->get(route(self::MODEL . '.undelete', ['archive_id' => $archive->id]));

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
        $this->setUser('admin');

        $response = $this->get(route(self::MODEL . '.create'));

        $response->assertStatus(200);
    }

    // public function test_store()
    // {
    //     $this->setUser('admin');
    //     $planning = Planning::factory()->create(['is_validated' => 1]);
    //     $archive = Archive::factory()
    //         ->make([
    //             'planning_id' => $planning->id,
    //             'event_id' => $planning->id,
    //         ]);
    //     $data = array_merge($archive->toArray());

    //     $response = $this->post(route(self::MODEL . '.store'), $data);

    //     $response->assertSessionHas('ok');
    // }

    public function test_edit()
    {
        $this->setUser('admin');
        $planning = Planning::factory()->create(['is_validated' => 1]);

        $archive = Archive::factory()
            ->create();

        $response = $this->get(route(self::MODEL . '.edit', ['archive' => $archive->id]));

        $response->assertStatus(200);
    }

    // public function test_update()
    // {
    //     $this->setUser('admin');
    //     $planning = Planning::factory()->create(['is_validated' => 1]);
    //     $archive = Archive::factory()
    //         ->create([
    //             'planning_id' => $planning->id,
    //         ]);

    //     $data = array_merge($archive->toArray(), [
    //         'nom' => 'Updated Name',
    //     ]);

    //     $response = $this->put(route(self::MODEL . '.update', ['archive' => $archive->id]), $data);

    //     $response->assertSessionHasNoErrors();

    //     $updatedArchive = Archive::find($archive->id);
    //     $this->assertNotNull($updatedArchive);
    //     $this->assertNotNull($updatedArchive->user_id_modification);
    //     $response->assertSessionHas('ok');
    // }

    public function test_delete()
    {
        $this->setUser('admin');
        $planning = Planning::factory()->create(['is_validated' => 1]);

        $archive = Archive::factory()
            ->create();

        $response = $this->delete(route(self::MODEL . '.destroy', ['archive' => $archive->id]));

        $this->assertSoftDeleted(Archive::class);
        $response->assertSessionHas(['ok']);
    }

    public function test_undelete()
    {
        $this->setUser('admin');
        $planning = Planning::factory()->create(['is_validated' => 1]);

        $archive = Archive::factory()
            ->create();

        $response = $this->delete(route(self::MODEL . '.destroy', ['archive' => $archive->id]));
        $this->assertSoftDeleted(Archive::class);
        $response->assertSessionHas(['ok']);

        $response = $this->get(route(self::MODEL . '.undelete', ['archive_id' => $archive->id]));

        $this->assertNull($archive->user_id_suppression);
        $response->assertSessionHas(['ok']);
    }

    public function test_json()
    {
        $this->setUser('admin');

        $response = $this->get(route(self::MODEL . '.json'));

        $response->assertJsonStructure();
    }

    public function test_archivate()
    {
        $this->setUser('admin');

        $planning = Planning::factory()->create();

        $response = $this->post(route('archive.archivate', ['planning_id' => $planning->id]));

        $this->assertDatabaseHas('archives', [
            'planning_id' => $planning->id,
        ]);

        $response->assertSessionHas('ok', 'Archivage effectuée');

        $response->assertRedirect();

        $archive = Archive::where('planning_id', $planning->id)->first();
        $this->assertNotNull($archive, "L'archive n'a pas été créée.");

        $this->assertEquals($planning->id, $archive->planning_id);
        $this->assertEquals($planning->user_id, $archive->user_id);
        $this->assertEquals($planning->nom, $archive->nom);
    }

    public function test_archivate_already_exists()
{
    $user = $this->setUser('admin');

    $planning = Planning::factory()->create();

    Archive::create([
        'planning_id' => $planning->id,
        'user_id' => $planning->user_id,
        'nom' => $planning->nom,
        'lieu' => $planning->lieu->nom,
        'plannifier_le' => $planning->plannifier_le,
        'heure_debut' => $planning->heure_debut,
        'heure_fin' => $planning->heure_fin,
        'duree_tache' => Carbon::parse($planning->heure_debut)->diffInMinutes(Carbon::parse($planning->heure_fin)),
        'user_id_creation' => $user->id,
    ]);

    $response = $this->post(route('archive.archivate', ['planning_id' => $planning->id]));

    $response->assertSessionHas('erreur', 'Cette tache à déjà été archivée');

    $this->assertEquals(1, Archive::where('planning_id', $planning->id)->count());
}

    public function test_cannot_archivate_existing_archive()
    {
        $user = $this->setUser('admin');

        $planning = Planning::factory()->create(['user_id' => $user->id, 'is_validated' => true]);
        $data = $planning->toArray();
        $data['planning_id'] = $data['id'];
        $lieu = Lieu::find($data['lieu_id']);
        $data['lieu'] = $lieu->nom;
        unset($data['lieu_id']);
        Archive::factory()->create([
            'user_id' => $user->id,
            'planning_id' => $planning->id,
        ]);

        $response = $this->post(route(self::MODEL . '.archivate', ['planning_id' => $planning->id]), $data);

        $response->assertSessionHas('erreur');
        $response->assertStatus(302);

    }
}
