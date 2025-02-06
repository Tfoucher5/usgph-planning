<?php

namespace Tests\Feature\Models\Conge;

use App\Models\Conge\Motif;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class MotifTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * @var string
     */
    private const MODEL = 'motif';

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
        $motif = Motif::factory()
            ->create();

        $response = $this->get(route(self::MODEL . '.show', ['motif' => $motif->id]));

        $response->assertRedirect('login');
    }

    public function test_edit_need_login()
    {
        $motif = Motif::factory()
            ->create();

        $response = $this->get(route(self::MODEL . '.edit', ['motif' => $motif->id]));

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
        $motif = Motif::factory()
            ->make();
        $data = array_merge($motif->toArray());

        $response = $this->post(route(self::MODEL . '.store'), $data);
        $response->assertUnauthorized();
    }

    public function test_show_need_admin()
    {
        $this->setUser();
        $motif = Motif::factory()
            ->create();

        $response = $this->get(route(self::MODEL . '.show', ['motif' => $motif->id]));

        $response->assertUnauthorized();
    }

    public function test_edit_need_admin()
    {
        $this->setUser();
        $motif = Motif::factory()
            ->create();

        $response = $this->get(route(self::MODEL . '.edit', ['motif' => $motif->id]));

        $response->assertUnauthorized();
    }

    public function test_update_need_admin()
    {
        $this->setUser();
        $motif = Motif::factory()
            ->create();
        $data = array_merge($motif->toArray());
        $data['id'] = $motif->id;

        $response = $this->put(route(self::MODEL . '.update', ['motif' => $motif->id]), $data);

        $response->assertUnauthorized();
    }

    // public function test_delete_need_admin()
    // {
    //     $this->setUser();
    //     $motif = Motif::factory()
    //         ->create();

    //     $response = $this->delete(route(self::MODEL . '.destroy', ['motif' => $motif->id]));

    //     $response->assertUnauthorized();
    // }

    // public function test_undelete_need_admin()
    // {
    //     $this->setUser();
    //     $motif = Motif::factory()
    //         ->create();

    //     $response = $this->get(route(self::MODEL . '.undelete', ['motif_id' => $motif->id]));

    //     $response->assertUnauthorized();
    // }

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

    public function test_store()
    {
        $this->setUser('admin');

        $motif = Motif::factory()
            ->make();
        $data = array_merge($motif->toArray());

        $response = $this->post(route(self::MODEL . '.store'), $data);

        $response->assertSessionHas('ok');
    }

    public function test_edit()
    {
        $this->setUser('admin');

        $motif = Motif::factory()
            ->create();

        $response = $this->get(route(self::MODEL . '.edit', ['motif' => $motif->id]));

        $response->assertStatus(200);
    }

    public function test_update()
    {
        $this->setUser('admin');

        $motif = Motif::factory()
            ->create();
        $data = array_merge($motif->toArray());

        $response = $this->put(route(self::MODEL . '.update', ['motif' => $motif->id]), $data);
        $motif = Motif::find($motif->id);

        $this->assertNotNull($motif->user_id_modification);
        $response->assertSessionHas('ok');
    }

    // public function test_delete()
    // {
    //     $this->setUser('admin');

    //     $motif = Motif::factory()
    //         ->create();

    //     $response = $this->delete(route(self::MODEL . '.destroy', ['motif' => $motif->id]));

    //     $this->assertSoftDeleted(Motif::class);
    //     $response->assertSessionHas(['ok']);
    // }

    // public function test_undelete()
    // {
    //     $this->setUser('admin');

    //     $motif = Motif::factory()->create(['id' => 11]);

    //     $this->delete(route(self::MODEL . '.destroy', ['motif' => $motif->id]));

    //     $motif = Motif::withTrashed()->find($motif->id);
    //     $this->assertNotNull($motif->deleted_at);
    //     $this->assertSoftDeleted($motif);

    //     $response = $this->get(route(self::MODEL . '.undelete', ['motif_id' => 11]));

    //     $motif = Motif::find($motif->id);
    //     $this->assertNull($motif->deleted_at);
    //     $this->assertFalse($motif->trashed());
    //     $response->assertSessionHas(['ok']);
    // }

    public function test_json()
    {
        $this->setUser('admin');

        $response = $this->get(route(self::MODEL . '.json'));

        $response->assertJsonStructure();
    }

    public function test_corbeille_need_login()
    {
        $response = $this->get(route(self::MODEL . '.corbeille'));

        $response->assertRedirect('login');
    }

    public function test_corbeille_need_admin()
    {
        $this->setUser();

        $response = $this->get(route(self::MODEL . '.corbeille'));

        $response->assertUnauthorized();
    }

    public function test_corbeille()
    {
        $this->setUser('admin');

        $response = $this->get(route(self::MODEL . '.corbeille'));

        $response->assertStatus(200);

    }
}
