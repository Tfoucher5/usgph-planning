<?php

namespace Tests\Feature;

use App\Models\Commun\Role;
use App\Support\RoleModelHook;
use Barryvdh\LaravelIdeHelper\Console\ModelsCommand;
use Illuminate\Database\Eloquent\Model;
use Mockery;
use Tests\TestCase;

class RoleModelHookTest extends TestCase
{
    public function test_run_sets_method_and_property_for_role_model()
    {
        // Mock the Role model
        $roleModelMock = Mockery::mock(Role::class);

        // Mock the ModelsCommand
        $commandMock = Mockery::mock(ModelsCommand::class);
        $commandMock->shouldReceive('setMethod')
            ->once()
            ->with(
                'whereAssignedTo',
                '\Illuminate\Database\Eloquent\Builder',
                'whereAssignedTo($model, ?array<int|string> $keys = null)'
            )
            ->andReturnSelf();
        $commandMock->shouldReceive('setProperty')
            ->once()
            ->with(
                'assignedTo',
                'array<int|string>|null',
                null
            )
            ->andReturnSelf();

        // Instantiate the hook
        $hook = new RoleModelHook;

        // Run the hook
        $hook->run($commandMock, $roleModelMock);

        // Assertions to confirm methods were called
        $this->addToAssertionCount(1); // Confirming that `setMethod` was called
        $this->addToAssertionCount(1); // Confirming that `setProperty` was called
    }

    public function test_run_skips_for_non_role_model()
    {
        // Mock a non-Role model
        $nonRoleModelMock = Mockery::mock(Model::class);

        // Mock the ModelsCommand
        $commandMock = Mockery::mock(ModelsCommand::class);
        $commandMock->shouldNotReceive('setMethod');
        $commandMock->shouldNotReceive('setProperty');

        // Instantiate the hook
        $hook = new RoleModelHook;

        // Run the hook
        $hook->run($commandMock, $nonRoleModelMock);

        // Assert no methods were called
        $this->assertTrue(true); // Passes if no exceptions occur
    }

    public function test_logs_messages_correctly()
    {
        // Mock the Role model
        $roleModelMock = Mockery::mock(\App\Models\Commun\Role::class);

        // Mock the ModelsCommand
        $commandMock = Mockery::mock(ModelsCommand::class);

        // Mock the methods that will be called on ModelsCommand
        $commandMock->shouldReceive('setMethod')
            ->once()
            ->with(
                'whereAssignedTo',
                '\Illuminate\Database\Eloquent\Builder',
                'whereAssignedTo($model, ?array<int|string> $keys = null)'
            );

        $commandMock->shouldReceive('setProperty')
            ->once()
            ->with(
                'assignedTo',
                'array<int|string>|null',
                null
            );

        // Use Laravel's Log facade with fake
        \Log::shouldReceive('info')
            ->once()
            ->with('RoleModelHook is running...');
        \Log::shouldReceive('info')
            ->once()
            ->with('Model class: ' . get_class($roleModelMock)); // Adjust to match the mock class
        \Log::shouldReceive('info')
            ->once()
            ->with('Role model identified.');

        // Instantiate the hook
        $hook = new RoleModelHook;

        // Run the hook
        $hook->run($commandMock, $roleModelMock);

        // Assert logs were written
        $this->addToAssertionCount(1); // Placeholder assertion for log expectations
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
