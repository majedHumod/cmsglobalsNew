<?php

namespace Tests\Feature;

use App\Events\BookingLifecycleChanged;
use App\Events\CheckInSubmitted;
use App\Events\HabitLogRecorded;
use App\Events\MembershipLifecycleChanged;
use App\Http\Resources\Api\ClientHomeResource;
use App\Http\Resources\Api\HabitResource;
use App\Http\Resources\Api\MessageResource;
use App\Http\Resources\Api\MessageThreadResource;
use App\Http\Resources\Api\NotificationResource;
use App\Jobs\EvaluateClientSignalsJob;
use App\Listeners\SendBookingLifecycleNotifications;
use App\Listeners\SendCheckInNotifications;
use App\Listeners\SendHabitLogNotifications;
use App\Listeners\SendMembershipLifecycleNotifications;
use App\Models\Habit;
use App\Models\Message;
use App\Models\ProgressCheckIn;
use App\Models\SessionBooking;
use App\Models\User;
use App\Models\UserMembership;
use Illuminate\Events\CallQueuedListener;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PhaseTwoGapClosureTest extends TestCase
{
    public function test_phase_two_routes_are_registered(): void
    {
        $this->assertTrue(Route::has('messages.broadcast'));
        $this->assertTrue(Route::has('messages.templates.store'));

        $this->assertTrue(Route::has('api.messages.templates'));
        $this->assertTrue(Route::has('api.messages.broadcast'));

        $this->assertTrue(Route::has('api.v1.client.home'));
        $this->assertTrue(Route::has('api.v1.messages.send'));
        $this->assertTrue(Route::has('api.v1.habits.today'));
        $this->assertTrue(Route::has('api.v1.notifications.index'));
    }

    public function test_phase_two_listeners_are_queueable(): void
    {
        $this->assertContains(ShouldQueue::class, class_implements(SendBookingLifecycleNotifications::class));
        $this->assertContains(ShouldQueue::class, class_implements(SendCheckInNotifications::class));
        $this->assertContains(ShouldQueue::class, class_implements(SendMembershipLifecycleNotifications::class));
        $this->assertContains(ShouldQueue::class, class_implements(SendHabitLogNotifications::class));
    }

    public function test_phase_two_events_queue_listeners(): void
    {
        Queue::fake();

        Event::dispatch(new BookingLifecycleChanged(new SessionBooking(), 'updated'));
        Event::dispatch(new CheckInSubmitted(new ProgressCheckIn()));
        Event::dispatch(new MembershipLifecycleChanged(new UserMembership()));
        Event::dispatch(new HabitLogRecorded(new Habit(), new User()));

        Queue::assertPushed(CallQueuedListener::class, fn ($job) => $job->class === SendBookingLifecycleNotifications::class);
        Queue::assertPushed(CallQueuedListener::class, fn ($job) => $job->class === SendCheckInNotifications::class);
        Queue::assertPushed(CallQueuedListener::class, fn ($job) => $job->class === SendMembershipLifecycleNotifications::class);
        Queue::assertPushed(CallQueuedListener::class, fn ($job) => $job->class === SendHabitLogNotifications::class);
    }

    public function test_phase_two_resource_contracts_include_expected_keys(): void
    {
        $message = new Message(['conversation_id' => 1, 'sender_user_id' => 2, 'body' => 'test']);
        $message->id = 10;
        $message->setRelation('sender', new User(['name' => 'Tester']));

        $messageData = (new MessageResource($message))->toArray(request());
        $this->assertArrayHasKey('conversation_id', $messageData);
        $this->assertArrayHasKey('body', $messageData);

        $habit = new Habit(['name' => 'Water', 'target_value' => 8, 'is_active' => true]);
        $habit->setRelation('logs', collect());
        $habitData = (new HabitResource($habit))->toArray(request());
        $this->assertArrayHasKey('today_log', $habitData);

        $notification = new \App\Models\NotificationFeed(['type' => 'x', 'title' => 'y']);
        $notificationData = (new NotificationResource($notification))->toArray(request());
        $this->assertArrayHasKey('type', $notificationData);

        $thread = new \App\Models\Conversation(['subject' => 'sub']);
        $thread->setRelation('participants', collect());
        $thread->setRelation('messages', collect());
        $threadData = (new MessageThreadResource($thread))->toArray(request());
        $this->assertArrayHasKey('participants', $threadData);

        $homeData = (new ClientHomeResource([
            'date' => now()->toDateString(),
            'progress_score' => 77.2,
            'weekly_habit_completion' => 65.5,
            'next_best_action' => 'do next',
            'bookings' => collect(),
            'habits' => collect(),
            'latest_notification' => null,
            'last_check_in' => null,
        ]))->toArray(request());
        $this->assertArrayHasKey('progress_score', $homeData);
        $this->assertArrayHasKey('next_best_action', $homeData);
    }

    public function test_phase_two_queue_job_is_dispatchable(): void
    {
        Queue::fake();
        EvaluateClientSignalsJob::dispatch(123);
        Queue::assertPushed(EvaluateClientSignalsJob::class);
    }
}
