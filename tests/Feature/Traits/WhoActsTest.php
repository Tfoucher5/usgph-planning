<?php

namespace Tests\Unit\Traits;

use App\Models\Planning\Planning;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhoActsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_creation_relationship()
    {
        $user = User::factory()->create();
        $planning = Planning::factory()->create([
            'user_id_creation' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $planning->userCreation);
        $this->assertEquals($user->id, $planning->userCreation->id);
    }

    public function test_user_modification_relationship()
    {
        $user = User::factory()->create();
        $planning = Planning::factory()->create([
            'user_id_modification' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $planning->userModification);
        $this->assertEquals($user->id, $planning->userModification->id);
    }

    public function test_user_suppression_relationship()
    {
        $user = User::factory()->create();
        $planning = Planning::factory()->create([
            'user_id_suppression' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $planning->userSuppression);
        $this->assertEquals($user->id, $planning->userSuppression->id);
    }
}
