<?php

namespace Tests\Feature\Models\Commun;

use App\Models\Commun\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Silber\Bouncer\Database\Ability;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function actions_attribute_is_accessible()
    {
        $role = Role::factory()->create();

        $this->assertEmpty($role->actions);
    }

    /** @test */
    public function a_role_can_be_assigned_to_user()
    {
        $bouncer = app(\Silber\Bouncer\Bouncer::class);

        $role = $bouncer->role()->create(['name' => 'admin']);

        $user = \App\Models\User::factory()->create();

        $user->assign($role);

        $this->assertTrue($user->isA('admin'));
    }

    /** @test */
    public function a_role_has_abilities()
    {
        $role = Role::factory()->create();

        $ability = Ability::create(['name' => 'edit_posts']);
        $role->allow($ability);

        $this->assertTrue($role->abilities->contains($ability));
    }

    /** @test */
    public function a_role_can_be_created_with_specific_data()
    {
        $role = Role::create([
            'name' => 'manager',
            'title' => 'Manager',
            'scope' => 1,
        ]);

        $this->assertEquals('manager', $role->name);
        $this->assertEquals('Manager', $role->title);
        $this->assertEquals(1, $role->scope);
    }
}
