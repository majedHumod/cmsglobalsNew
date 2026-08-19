<?php

namespace App\Http\Controllers;

use App\Models\SessionBooking;
use Carbon\Carbon;
use Illuminate\Http\Response;

class BookingCalendarController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function download(SessionBooking $sessionBooking): Response
    {
        abort_unless($sessionBooking->canManage(auth()->user()), 403);

        $sessionBooking->loadMissing('trainingSession', 'user');

        $startAt = Carbon::parse($sessionBooking->booking_date->format('Y-m-d') . ' ' . $sessionBooking->booking_time->format('H:i:s'));
        $endAt = $startAt->copy()->addHours((int) optional($sessionBooking->trainingSession)->duration_hours ?: 1);
        $uid = $sessionBooking->calendar_uid ?: ('booking-' . $sessionBooking->id . '@cmsglobals');

        if (! $sessionBooking->calendar_uid) {
            $sessionBooking->update(['calendar_uid' => $uid]);
        }

        $content = "BEGIN:VCALENDAR\r\n";
        $content .= "VERSION:2.0\r\n";
        $content .= "PRODID:-//CMSGlobals//Bookings//AR\r\n";
        $content .= "BEGIN:VEVENT\r\n";
        $content .= "UID:{$uid}\r\n";
        $content .= "DTSTAMP:" . now()->utc()->format('Ymd\THis\Z') . "\r\n";
        $content .= "DTSTART:" . $startAt->utc()->format('Ymd\THis\Z') . "\r\n";
        $content .= "DTEND:" . $endAt->utc()->format('Ymd\THis\Z') . "\r\n";
        $content .= "SUMMARY:" . $this->escapeText(optional($sessionBooking->trainingSession)->title ?: 'Training Session') . "\r\n";
        $content .= "DESCRIPTION:" . $this->escapeText($sessionBooking->notes ?: 'Training session booking') . "\r\n";
        $content .= "LOCATION:" . $this->escapeText(optional($sessionBooking->trainingSession)->location ?: '') . "\r\n";
        if ($sessionBooking->video_meeting_url) {
            $content .= "URL:" . $this->escapeText($sessionBooking->video_meeting_url) . "\r\n";
        }
        $content .= "END:VEVENT\r\n";
        $content .= "END:VCALENDAR\r\n";

        $filename = 'booking-' . $sessionBooking->id . '.ics';

        return response($content, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function escapeText(string $text): string
    {
        return str_replace(["\\", ";", ",", "\n", "\r"], ["\\\\", "\;", "\,", "\\n", ''], $text);
    }
}
