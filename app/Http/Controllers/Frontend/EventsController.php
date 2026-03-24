<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Support\Calendar;
use Carbon\Carbon;

class EventsController extends Controller
{
    public function index()
    {
        // Upcoming events with dates
        $datedEvents = Event::query()
            ->where('has_dates', true)
            ->whereNotNull('starts_at')
            ->where('starts_at', '>=', now()->startOfDay())
            ->orderBy('starts_at')
            ->get();

        // Events without dates (old CMS behavior)
        $undatedEvents = Event::query()
            ->where('has_dates', false)
            ->orderByDesc('created_at')
            ->get();

        $events = $datedEvents->merge($undatedEvents);

        return view()->first(
            [get_theme() . '.events', 'default.events'],
            compact('events')
        );
    }

    public function calendar(?int $month = null, ?int $year = null)
    {
        $month = $month ?? now()->month;
        $year  = $year ?? now()->year;

        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        $events = Event::query()
            ->where('has_dates', true)
            ->whereBetween('starts_at', [$start, $end])
            ->get()
            ->groupBy(fn ($event) => $event->starts_at->format('Y-m-d'));

        $calendar = new Calendar($month, $year);

        return view()->first(
            [get_theme() . '.calendar', 'default.calendar'],
            compact('events', 'month', 'year', 'calendar')
        );
    }

    public function show(Event $event)
    {
        return view()->first(
            [get_theme() . '.event', 'default.event'],
            compact('event')
        );
    }
}