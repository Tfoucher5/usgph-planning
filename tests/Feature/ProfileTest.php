<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    // public function test_profile_page_is_displayed(): void
    // {
    //     $user = User::factory()->create();

    //     $response = $this
    //         ->actingAs($user)
    //         ->get('/profile');

    //     $response->assertOk();
    // }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();
        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'first_name' => 'test',
                'last_name' => 'user',
                'email' => 'testuser@usgph.com',
            ]);
        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();
        $this->assertSame('test', $user->first_name);
        $this->assertSame('user', $user->last_name);
        $this->assertSame('testuser@usgph.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'first_name' => 'test',
                'last_name' => 'user',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }

    /** @test */
    public function it_displays_the_edit_profile_form()
    {
        $user = $this->setUser();

        $response = $this->get(route('profile.edit'));

        $response->assertViewIs('profile.edit');

        $response->assertViewHas('user', $user);
    }
}
