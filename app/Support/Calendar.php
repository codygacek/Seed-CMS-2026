<?php

declare(strict_types=1);

namespace App\Support;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class Calendar
{
    private CarbonImmutable $monthDate;

    public function __construct(?int $month = null, ?int $year = null)
    {
        $month = $month ?: (int) now()->format('m');
        $year  = $year  ?: (int) now()->format('Y');

        // Safe, immutable reference point: first day of requested month
        $this->monthDate = CarbonImmutable::create($year, $month, 1)->startOfDay();
    }

    public function make(array|Collection|null $events = null): string
    {
        if ($events instanceof Collection) {
            $events = $events->map(fn ($items) => $items->all())->all();
        }

        return $this->getHeader()
            . $this->getHeaders()
            . $this->getPreviousMonthFillerDays()
            . $this->getCurrentMonthDays($events)
            . $this->getNextMonthFillerDays();
    }

    public function getHeader(): string
    {
        return '<header class="calendar-header">'
            . '<a class="calendar-control is-previous" href="' . e($this->getPreviousLink()) . '"><span class="fas fa-arrow-left"></span></a>'
            . $this->getMonthHeading()
            . '<a class="calendar-control is-next" href="' . e($this->getNextLink()) . '"><span class="fas fa-arrow-right"></span></a>'
            . '</header>';
    }

    public function getMonthHeading(): string
    {
        return '<span><strong>' . e($this->monthDate->format('F')) . '</strong> ' . e($this->monthDate->format('Y')) . '</span>';
    }

    public function getPreviousLink(): string
    {
        $prev = $this->monthDate->subMonthNoOverflow();

        return '/calendar/' . (int) $prev->format('n') . '/' . (int) $prev->format('Y');
    }

    public function getNextLink(): string
    {
        $next = $this->monthDate->addMonthNoOverflow();

        return '/calendar/' . (int) $next->format('n') . '/' . (int) $next->format('Y');
    }

    public function getHeaders(): string
    {
        $daysOfWeek = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        $html = '';
        foreach ($daysOfWeek as $day) {
            $html .= '<div class="calendar-column is-header">' . e(substr($day, 0, 3)) . '</div>';
        }

        return $html;
    }

    public function getPreviousMonthFillerDays(bool $showFillerDayNumbers = true): string
    {
        // 0 (Sun) .. 6 (Sat)
        $weekdayOfFirst = (int) $this->monthDate->format('w');

        // If month starts on Sunday, no previous-month fillers needed.
        if ($weekdayOfFirst === 0) {
            return '';
        }

        $prevMonth = $this->monthDate->subMonthNoOverflow();
        $daysInPrevMonth = (int) $prevMonth->endOfMonth()->format('j');

        $startDay = $daysInPrevMonth - ($weekdayOfFirst - 1);
        $days = range($startDay, $daysInPrevMonth);

        $html = '';
        foreach ($days as $day) {
            $html .= '<div class="calendar-column">';
            $html .= '<span class="day-indicator">';
            if ($showFillerDayNumbers) {
                $html .= e((string) $day);
            }
            $html .= '</span>';
            $html .= '</div>';
        }

        return $html;
    }

    public function getCurrentMonthDays(?array $events = null): string
    {
        $daysInMonth = (int) $this->monthDate->endOfMonth()->format('j');
        $todayKey = now()->format('Y-m-d');

        $year  = $this->monthDate->format('Y');
        $month = $this->monthDate->format('m');

        $html = '';

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dateKey = $year . '-' . $month . '-' . sprintf('%02d', $day);

            $modifierClasses = ($dateKey === $todayKey) ? ' today' : '';

            $html .= '<div class="calendar-column is-current-month">';
            $html .= '<span class="day-indicator' . $modifierClasses . '">' . e((string) $day) . '</span>';

            if (is_array($events) && isset($events[$dateKey])) {
                $html .= '<div class="event-list">';
                foreach ($events[$dateKey] as $event) {
                    // Keep legacy URLs; escape title for safety
                    $html .= '<a href="/events/' . e($event->slug) . '">' . e($event->title) . '</a><br>';
                }
                $html .= '</div>';
            }

            $html .= '</div>';
        }

        return $html;
    }

    public function getNextMonthFillerDays(bool $showFillerDayNumbers = true): string
    {
        $lastDay = $this->monthDate->endOfMonth();
        $weekdayOfLast = (int) $lastDay->format('w'); // 0..6

        $needed = 6 - $weekdayOfLast;
        if ($needed <= 0) {
            return '';
        }

        $html = '';
        for ($day = 1; $day <= $needed; $day++) {
            $html .= '<div class="calendar-column">';
            $html .= '<span class="day-indicator">';
            if ($showFillerDayNumbers) {
                $html .= e((string) $day);
            }
            $html .= '</span>';
            $html .= '</div>';
        }

        return $html;
    }

    // Optional: expose month/year for controllers/views
    public function month(): int
    {
        return (int) $this->monthDate->format('n');
    }

    public function year(): int
    {
        return (int) $this->monthDate->format('Y');
    }
}