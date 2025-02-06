<?php

namespace Tests\Feature\Console;

use App\Console\Kernel;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Mockery;
use Tests\TestCase;

class KernelTest extends TestCase
{
    /**
     * Test if the schedule method registers commands correctly.
     */
    public function test_schedule_method()
    {
        $schedule = Mockery::mock(Schedule::class);

        // Configure le mock pour s'attendre à l'appel de command()
        // et retourner un Event mock qui acceptera l'appel à weeklyOn()
        $event = Mockery::mock(Event::class);
        $event->shouldReceive('weeklyOn')
            ->with(1, '8:00')
            ->once()
            ->andReturnSelf();

        $schedule->shouldReceive('command')
            ->with('notify:weekly')
            ->once()
            ->andReturn($event);

        // Créer une instance du Kernel
        $kernel = $this->app->make(Kernel::class);

        // Accéder à la méthode protégée 'schedule'
        $reflection = new \ReflectionMethod(Kernel::class, 'schedule');
        $reflection->setAccessible(true);

        // Exécuter la méthode schedule
        $reflection->invoke($kernel, $schedule);
    }

    /**
     * Test if the commands method loads commands correctly.
     */
    public function test_commands_method()
    {
        // Créer un mock de l'application
        $app = Mockery::mock(Application::class);
        $app->shouldReceive('getNamespace')->andReturn('App\\');

        // Créer un mock partiel du Kernel avec l'application mockée et activer le mock des méthodes protégées
        $kernel = Mockery::mock(Kernel::class)->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $kernel->shouldReceive('load')
            ->once()
            ->with(base_path('app/Console/Commands'))
            ->andReturn(null);

        // Injecter le mock de l'application dans le kernel
        $reflection = new \ReflectionProperty(Kernel::class, 'app');
        $reflection->setAccessible(true);
        $reflection->setValue($kernel, $app);

        // Exécuter la méthode commands
        $kernel->commands();

        // Vérifier explicitement que la méthode load a été appelée comme prévu
        $this->assertTrue(Mockery::close() !== false, 'La méthode load() n\'a pas été appelée comme attendu');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
}
