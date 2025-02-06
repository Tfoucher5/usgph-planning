<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WelcomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_is_a_returns_true_for_assigned_role()
    {
        $user = $this->setUser('salarie');

        $result = $user->isA('salarie');

        $this->assertTrue($result);
    }

    public function test_is_a_returns_false_for_assigned_role()
    {
        $user = $this->setUser('admin');

        $result = $user->isA('salarie');

        $this->assertFalse($result);
    }

    public function test_redirect_to_planning_index_for_salarie_user()
    {
        $this->setUser('salarie');

        $response = $this->get(route('home'));

        $response->assertRedirect(route('planning.index'));
    }

    public function test_redirect_to_synthese_for_non_salarie_user()
    {
        $this->setUser('admin');

        $response = $this->get(route('home'));

        $response->assertRedirect(route('synthese.index'));
    }

    public function test_redirect_for_guest_user()
    {
        $response = $this->get(route('home'));

        $response->assertRedirect(route('login'));
    }
}
