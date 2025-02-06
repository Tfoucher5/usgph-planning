<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Notifications\PlanningNotification;
use Illuminate\Support\Facades\Notification;

class PlanningNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_is_sent()
    {
        Notification::fake();

        $user = User::factory()->create();

        $details = [
            'subject' => 'Test Subject',
            'message' => 'Test Message',
            'actionText' => 'View',
            'actionUrl' => url('/test')
        ];

        $notification = new PlanningNotification($details);
        $user->notify($notification);

        Notification::assertSentTo($user, PlanningNotification::class);

        $mailData = $notification->toMail($user);
        $this->assertEquals('Test Subject', $mailData->subject);
        $this->assertStringContainsString('Test Message', $mailData->introLines[0]);
        $this->assertEquals(url('/test'), $mailData->actionUrl);

        $arrayData = $notification->toArray($user);
        $this->assertIsArray($arrayData);
    }
}
