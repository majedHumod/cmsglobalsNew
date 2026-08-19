<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientHomeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'date' => $this['date'],
            'progress_score' => $this['progress_score'],
            'weekly_habit_completion' => $this['weekly_habit_completion'],
            'workout_compliance' => $this['workout_compliance'] ?? 0,
            'current_program_week' => $this['current_program_week'] ?? 1,
            'next_best_action' => $this['next_best_action'],
            'progress_overview' => $this['progress_overview'] ?? [
                'title' => 'تقدمك الكلي',
                'score' => $this['progress_score'] ?? 0,
                'headline' => $this['next_best_action'] ?? '',
                'trend' => 'steady',
                'habits_percent' => $this['weekly_habit_completion'] ?? 0,
                'workouts_percent' => $this['workout_compliance'] ?? 0,
                'program_week' => $this['current_program_week'] ?? 1,
                'next_best_action' => $this['next_best_action'] ?? '',
            ],
            'bookings' => collect($this['bookings'])->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'training_session_id' => $booking->training_session_id,
                    'title' => optional($booking->trainingSession)->title,
                    'booking_date' => optional($booking->booking_date)->toDateString(),
                    'booking_time' => optional($booking->booking_time)->format('H:i'),
                    'status' => $booking->status,
                    'video_meeting_url' => $booking->video_meeting_url ?: optional($booking->trainingSession)->video_meeting_url,
                    'calendar_url' => route('session-bookings.calendar', $booking),
                ];
            }),
            'habits' => HabitResource::collection($this['habits']),
            'habits_section' => [
                'title' => 'عادات اليوم',
                'view_all_label' => 'عرض كل العادات',
                'view_all_url' => url('/api/v1/habits/today'),
            ],
            'today_workouts' => WorkoutTodayResource::collection(collect($this['today_workouts'] ?? [])),
            'week_overview' => $this['week_overview'] ?? [],
            'week_overview_section' => $this['week_overview_section'] ?? [
                'title' => 'نظرة الأسبوع',
                'icon_key' => 'calendar',
                'legend' => [
                    ['key' => 'workout', 'label' => 'تمرين', 'color' => 'purple'],
                    ['key' => 'habit', 'label' => 'عادة', 'color' => 'green'],
                    ['key' => 'rest', 'label' => 'راحة', 'color' => 'grey'],
                ],
                'days' => $this['week_overview'] ?? [],
            ],
            'gamification' => $this['gamification'] ?? ['points' => 0, 'badges_count' => 0],
            'latest_notification' => $this['latest_notification']
                ? new NotificationResource($this['latest_notification'])
                : null,
            'latest_message' => ($this['latest_message'] ?? null) ? [
                'id' => $this['latest_message']->id,
                'body' => $this['latest_message']->body,
                'sent_at' => optional($this['latest_message']->sent_at)->toIso8601String(),
                'conversation_id' => $this['latest_message']->conversation_id,
            ] : null,
            // بطاقة «رسالة المدرب» — null إن لم تتوفر رسالة
            'coach_message' => $this['coach_message'] ?? null,
            'member_pages' => $this['member_pages'] ?? [],
            'member_pages_section' => $this['member_pages_section'] ?? [
                'title' => 'محتوى لك',
                'view_all_label' => 'عرض الكل',
                'view_all_url' => url('/client/pages'),
                'items' => $this['member_pages'] ?? [],
            ],
            'nutrition_card' => $this['nutrition_card'] ?? [
                'title' => 'التغذية اليومية',
                'subtitle' => 'التزامك: '.round((float) ($this['nutrition_adherence'] ?? 0)).'%',
                'adherence' => (float) ($this['nutrition_adherence'] ?? 0),
                'adherence_label' => round((float) ($this['nutrition_adherence'] ?? 0)).'%',
                'action_label' => 'سجّل وجبة',
                'enabled' => true,
                'endpoint' => '/api/v1/nutrition',
            ],
            'challenges_card' => $this['challenges_card'] ?? [
                'title' => 'التحديات',
                'subtitle' => 'شارك في التحديات واربح',
                'icon_key' => 'trophy',
                'action_label' => 'عرض التحديات',
                'enabled' => true,
                'endpoint' => '/api/v1/challenges',
            ],
            'community_card' => $this['community_card'] ?? [
                'title' => 'المجتمع',
                'subtitle' => 'تواصل وتفاعل مع الأعضاء',
                'icon_key' => 'community',
                'action_label' => 'فتح المجتمع',
                'enabled' => true,
                'endpoint' => '/api/v1/community/posts',
            ],
            'check_in_card' => $this['check_in_card'] ?? [
                'title' => 'المتابعة',
                'subtitle' => 'سجّل بياناتك لمتابعة تقدمك',
                'icon_key' => 'chart_up',
                'action_label' => 'تسجيل المتابعة',
                'enabled' => true,
                'endpoint' => '/api/v1/check-ins',
                'submit_endpoint' => '/api/v1/check-ins',
            ],
            'bookings_card' => $this['bookings_card'] ?? [
                'title' => 'الحجوزات',
                'subtitle' => 'عرض حجوزاتك القادمة والسابقة',
                'icon_key' => 'calendar',
                'action_label' => 'حجوزاتي',
                'enabled' => true,
                'endpoint' => '/api/v1/bookings',
                'create_endpoint' => '/api/v1/bookings/sessions',
            ],
            'unread_notifications_count' => (int) ($this['unread_notifications_count'] ?? 0),
            'unread_messages_count' => (int) ($this['unread_messages_count'] ?? 0),
            'communications_summary' => $this['communications_summary'] ?? null,
            'last_check_in' => $this['last_check_in'] ? [
                'id' => $this['last_check_in']->id,
                'checked_in_at' => optional($this['last_check_in']->checked_in_at)->toIso8601String(),
                'average_adherence' => $this['last_check_in']->average_adherence,
            ] : null,
            'check_in_url' => $this['check_in_url'] ?? url('/client/progress/create'),
            'messages_url' => $this['messages_url'] ?? null,
            'nutrition_url' => $this['nutrition_url'] ?? null,
            'community_url' => $this['community_url'] ?? null,
            'challenges_url' => $this['challenges_url'] ?? null,
            'pages_url' => $this['pages_url'] ?? null,
            'more_url' => $this['more_url'] ?? null,
            'nutrition_adherence' => $this['nutrition_adherence'] ?? 0,
            'membership_days_remaining' => $this['membership_days_remaining'] ?? null,
            'renew_url' => $this['renew_url'] ?? null,
        ];
    }
}
