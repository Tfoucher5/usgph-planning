<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Notifications\WeeklyPlanningNotification;
use Illuminate\Support\Facades\Notification;

class WeeklyPlanningNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_weekly_planning_notification_is_sent()
    {
        Notification::fake();

        $user = User::factory()->create();

        $user->notify(new WeeklyPlanningNotification());

        Notification::assertSentTo($user, WeeklyPlanningNotification::class);
    }
}
