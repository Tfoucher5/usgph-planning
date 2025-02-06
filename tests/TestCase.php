<?php

namespace tests;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\InvalidCastException;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use InvalidArgumentException;
use Silber\Bouncer\BouncerFacade as Bouncer;
use Tests\CreatesApplication;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * @param  null|string  $role
     * @param  null|string  $ability
     *
     * @return User|Authenticatable|null
     *
     * @throws InvalidCastException
     * @throws InvalidArgumentException
     */
    protected function setUser(?string $role = null, ?string $ability = null)
    {
        /**
         * @var User|Authenticatable
         */
        $user = User::factory()->create();

        if ($role !== null) {
            Bouncer::assign($role)->to($user);
        }

        if ($ability !== null) {
            $user->allow($ability);
        }

        $this->actingAs($user);

        return $user;
    }
}
