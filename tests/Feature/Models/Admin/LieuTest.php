<?php

namespace Tests\Feature\Models\Admin;

use App\Models\Admin\Lieu;
use App\Models\Planning\Planning;
use App\Models\Planning\Tache;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LieuTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * @var string
     */
    private const MODEL = 'lieu';

    public function test_lieu_exists()
    {
        $this->assertTrue(class_exists(Lieu::class), 'ce model existe');
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
        $lieu = Lieu::factory()
            ->create();

        $response = $this->get(route(self::MODEL . '.show', ['lieu' => $lieu->id]));

        $response->assertRedirect('login');
    }

    public function test_edit_need_login()
    {
        $lieu = Lieu::factory()
            ->create();

        $response = $this->get(route(self::MODEL . '.edit', ['lieu' => $lieu->id]));

        $response->assertRedirect('login');
    }

    public function test_corbeille_need_login()
    {
        $lieu = Lieu::factory()
            ->create();

        $response = $this->get(route(self::MODEL . '.corbeille'));

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
        $lieu = Lieu::factory()
            ->make();
        $data = array_merge($lieu->toArray());

        $response = $this->post(route(self::MODEL . '.store'), $data);
        $response->assertUnauthorized();
    }

    public function test_show_need_admin()
    {
        $this->setUser();
        $lieu = Lieu::factory()
            ->create();

        $response = $this->get(route(self::MODEL . '.show', ['lieu' => $lieu->id]));

        $response->assertUnauthorized();
    }

    public function test_edit_need_admin()
    {
        $this->setUser();
        $lieu = Lieu::factory()
            ->create();

        $response = $this->get(route(self::MODEL . '.edit', ['lieu' => $lieu->id]));

        $response->assertUnauthorized();
    }

    public function test_update_need_admin()
    {
        $this->setUser();
        $lieu = Lieu::factory()
            ->create();
        $data = array_merge($lieu->toArray());
        $data['id'] = $lieu->id;

        $response = $this->put(route(self::MODEL . '.update', ['lieu' => $lieu->id]), $data);

        $response->assertUnauthorized();
    }

    public function test_delete_need_admin()
    {
        $this->setUser();
        $lieu = Lieu::factory()
            ->create();

        $response = $this->delete(route(self::MODEL . '.destroy', ['lieu' => $lieu->id]));

        $response->assertUnauthorized();
    }

    public function test_corbeille_need_admin()
    {
        $this->setUser();

        $response = $this->get(route(self::MODEL . '.corbeille'));

        $response->assertUnauthorized();
    }

    public function test_undelete_need_admin()
    {
        $this->setUser();
        $lieu = Lieu::factory()
            ->create();

        $response = $this->get(route(self::MODEL . '.undelete', ['lieu_id' => $lieu->id]));

        $response->assertUnauthorized();
    }

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

    public function test_store()
    {
        $this->setUser('admin');

        $lieu = Lieu::factory()
            ->make();
        $data = array_merge($lieu->toArray());

        $response = $this->post(route(self::MODEL . '.store'), $data);

        $response->assertSessionHas('ok');
    }

    public function test_edit()
    {
        $this->setUser('admin');

        $lieu = Lieu::factory()
            ->create();

        $response = $this->get(route(self::MODEL . '.edit', ['lieu' => $lieu->id]));

        $response->assertStatus(200);
    }

    public function test_update()
    {
        $this->setUser('admin');

        $lieu = Lieu::factory()
            ->create();
        $data = array_merge($lieu->toArray());

        $response = $this->put(route(self::MODEL . '.update', ['lieu' => $lieu->id]), $data);
        $lieu = Lieu::find($lieu->id);

        $this->assertNotNull($lieu->user_id_modification);
        $response->assertSessionHas('ok');
    }

    public function test_delete()
    {
        $this->setUser('admin');

        $lieu = Lieu::factory()
            ->create();

        $response = $this->delete(route(self::MODEL . '.destroy', ['lieu' => $lieu->id]));

        $this->assertSoftDeleted(Lieu::class);
        $response->assertSessionHas(['ok']);
    }

    public function test_corbeille()
    {
        $this->setUser('admin');

        $response = $this->get(route(self::MODEL . '.corbeille'));

        $response->assertStatus(200);

    }

    public function test_undelete()
    {
        $this->setUser('admin');

        $lieu = Lieu::factory()
            ->create();

        $response = $this->delete(route(self::MODEL . '.destroy', ['lieu' => $lieu->id]));
        $this->assertSoftDeleted(Lieu::class);
        $response->assertSessionHas(['ok']);

        $response = $this->get(route(self::MODEL . '.undelete', ['lieu_id' => $lieu->id]));

        $this->assertNull($lieu->user_id_suppression);
        $this->assertNull($lieu->deleted_at);
        $this->assertDatabaseHas('lieux', [
            'id' => $lieu->id,
            'deleted_at' => null,
        ]);
        $response->assertSessionHas(['ok']);
        $response->assertRedirect(route(self::MODEL . '.index'));
    }

    // public function test_json()
    // {
    //     $this->setUser('admin');

    //     $response = $this->get(route(self::MODEL . '.json'));

    //     $response->assertJsonStructure();
    // }

    /** @test */
    public function a_lieu_can_be_created()
    {
        $user = User::factory()->create();

        $lieu = Lieu::factory()->create(['user_id_creation' => $user->id]);

        $this->assertDatabaseHas('lieux', [
            'id' => $lieu->id,
            'nom' => $lieu->nom,
            'user_id_creation' => $user->id,
        ]);
    }

    /** @test */
    public function a_lieu_can_have_many_taches()
    {
        $lieu = Lieu::factory()->create();

        $tache1 = Tache::factory()->create(['lieu_id' => $lieu->id]);
        $tache2 = Tache::factory()->create(['lieu_id' => $lieu->id]);

        $this->assertCount(2, $lieu->taches);
    }

    /** @test */
    public function a_lieu_can_have_many_plannings()
    {
        $lieu = Lieu::factory()->create();

        $tache1 = Planning::factory()->create(['lieu_id' => $lieu->id]);
        $tache2 = Planning::factory()->create(['lieu_id' => $lieu->id]);

        $this->assertCount(2, $lieu->plannings);
    }

    /** @test */
    public function actions_attribute_is_accessible()
    {
        $lieu = Lieu::factory()->create();

        $this->assertNotEmpty($lieu->actions);
    }

    /** @test */
    public function a_lieu_can_be_soft_deleted()
    {
        $lieu = Lieu::factory()->create();

        $lieu->delete();

        $this->assertSoftDeleted('lieux', [
            'id' => $lieu->id,
        ]);
    }

    /** @test */
    public function a_lieu_can_be_updated()
    {
        $lieu = Lieu::factory()->create();

        $lieu->update(['nom' => 'Nouveau Nom']);

        $this->assertDatabaseHas('lieux', [
            'id' => $lieu->id,
            'nom' => 'Nouveau Nom',
        ]);
    }

    /** @test */
    public function a_lieu_can_be_restored_after_soft_deleting()
    {
        $lieu = Lieu::factory()->create();

        $lieu->delete();

        $lieu->restore();

        $this->assertDatabaseHas('lieux', [
            'id' => $lieu->id,
            'deleted_at' => null,
        ]);
    }
}
