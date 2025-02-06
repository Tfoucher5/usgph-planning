<?php

namespace Tests\Feature\Models\Planning;

use App\Models\Planning\Categorie;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CategorieTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * @var string
     */
    private const MODEL = 'categorie';

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
        $categorie = Categorie::factory()
            ->create();

        $response = $this->get(route(self::MODEL . '.show', ['categorie' => $categorie->id]));

        $response->assertRedirect('login');
    }

    public function test_edit_need_login()
    {
        $categorie = Categorie::factory()
            ->create();

        $response = $this->get(route(self::MODEL . '.edit', ['categorie' => $categorie->id]));

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
        $categorie = Categorie::factory()
            ->make();
        $data = array_merge($categorie->toArray());

        $response = $this->post(route(self::MODEL . '.store'), $data);
        $response->assertUnauthorized();
    }

    public function test_show_need_admin()
    {
        $this->setUser();
        $categorie = Categorie::factory()
            ->create();

        $response = $this->get(route(self::MODEL . '.show', ['categorie' => $categorie->id]));

        $response->assertUnauthorized();
    }

    public function test_edit_need_admin()
    {
        $this->setUser();
        $categorie = Categorie::factory()
            ->create();

        $response = $this->get(route(self::MODEL . '.edit', ['categorie' => $categorie->id]));

        $response->assertUnauthorized();
    }

    public function test_update_need_admin()
    {
        $this->setUser();
        $categorie = Categorie::factory()
            ->create();
        $data = array_merge($categorie->toArray());
        $data['id'] = $categorie->id;

        $response = $this->put(route(self::MODEL . '.update', ['categorie' => $categorie->id]), $data);

        $response->assertUnauthorized();
    }

    public function test_delete_need_admin()
    {
        $this->setUser();
        $categorie = Categorie::factory()
            ->create();

        $response = $this->delete(route(self::MODEL . '.destroy', ['categorie' => $categorie->id]));

        $response->assertUnauthorized();
    }

    public function test_undelete_need_admin()
    {
        $this->setUser();
        $categorie = Categorie::factory()
            ->create();

        $response = $this->get(route(self::MODEL . '.undelete', ['categorie_id' => $categorie->id]));

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

    public function test_store()
    {
        $this->setUser('admin');

        $categorie = Categorie::factory()
            ->make();
        $data = array_merge($categorie->toArray());

        $response = $this->post(route(self::MODEL . '.store'), $data);

        $response->assertSessionHas('ok');
    }

    public function test_edit()
    {
        $this->setUser('admin');

        $categorie = Categorie::factory()
            ->create();

        $response = $this->get(route(self::MODEL . '.edit', ['categorie' => $categorie->id]));

        $response->assertStatus(200);
    }

    public function test_update()
    {
        $this->setUser('admin');

        $categorie = Categorie::factory()
            ->create();
        $data = array_merge($categorie->toArray());

        $response = $this->put(route(self::MODEL . '.update', ['categorie' => $categorie->id]), $data);
        $categorie = Categorie::find($categorie->id);

        $this->assertNotNull($categorie->user_id_modification);
        $response->assertSessionHas('ok');
    }

    public function test_delete()
    {
        $this->setUser('admin');

        $categorie = Categorie::factory()
            ->create();

        $response = $this->delete(route(self::MODEL . '.destroy', ['categorie' => $categorie->id]));

        $this->assertSoftDeleted(Categorie::class);
        $response->assertSessionHas(['ok']);
    }

    public function test_undelete()
    {
        $this->setUser('admin');

        $categorie = Categorie::factory()
            ->create();

        $response = $this->delete(route(self::MODEL . '.destroy', ['categorie' => $categorie->id]));
        $this->assertSoftDeleted(Categorie::class);
        $response->assertSessionHas(['ok']);

        $response = $this->get(route(self::MODEL . '.undelete', ['categorie_id' => $categorie->id]));

        $this->assertNull($categorie->user_id_suppression);
        $response->assertSessionHas(['ok']);
    }

    public function test_json()
    {
        $this->setUser('admin');

        $response = $this->get(route(self::MODEL . '.json'));

        $response->assertJsonStructure();
    }
}
