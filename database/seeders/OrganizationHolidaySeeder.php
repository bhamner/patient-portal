<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\OrganizationHoliday;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class OrganizationHolidaySeeder extends Seeder
{
    /**
     * Fixed-date US holidays (same date every year). Stored as recurring, one row per org.
     * Uses 2024 as the year for storage; recurring flag means it applies every year.
     */
    private function fixedHolidays(): array
    {
        return [
            [Carbon::create(2024, 1, 1), 'New Year\'s Day'],
            [Carbon::create(2024, 6, 19), 'Juneteenth'],
            [Carbon::create(2024, 7, 4), 'Independence Day'],
            [Carbon::create(2024, 11, 11), 'Veterans Day'],
            [Carbon::create(2024, 12, 25), 'Christmas Day'],
        ];
    }

    /**
     * Floating US holidays (vary by year). Seeded for multiple years, all recurring.
     */
    private function floatingHolidaysForYear(int $year): array
    {
        return [
            [$this->nthWeekdayOfMonth($year, 1, 1, 3), 'Martin Luther King Jr. Day'],
            [$this->nthWeekdayOfMonth($year, 2, 1, 3), 'Presidents\' Day'],
            [$this->lastWeekdayOfMonth($year, 5, 1), 'Memorial Day'],
            [$this->nthWeekdayOfMonth($year, 9, 1, 1), 'Labor Day'],
            [$this->nthWeekdayOfMonth($year, 10, 1, 2), 'Columbus Day'],
            [$this->nthWeekdayOfMonth($year, 11, 4, 4), 'Thanksgiving'],
        ];
    }

    private function nthWeekdayOfMonth(int $year, int $month, int $dayOfWeek, int $n): Carbon
    {
        $date = Carbon::create($year, $month, 1);
        $count = 0;
        while ($date->month === $month) {
            if ((int) $date->isoFormat('E') === $dayOfWeek) {
                $count++;
                if ($count === $n) {
                    return $date->copy();
                }
            }
            $date->addDay();
        }

        return Carbon::create($year, $month, 1);
    }

    private function lastWeekdayOfMonth(int $year, int $month, int $dayOfWeek): Carbon
    {
        $date = Carbon::create($year, $month, 1)->endOfMonth();
        while ((int) $date->isoFormat('E') !== $dayOfWeek) {
            $date->subDay();
        }

        return $date->copy();
    }

    public function run(): void
    {
        $organizations = Organization::all();
        if ($organizations->isEmpty()) {
            return;
        }

        $years = [now()->year, now()->year + 1, now()->year + 2];

        foreach ($organizations as $organization) {
            $organization->holidays()->delete();

            foreach ($this->fixedHolidays() as [$date, $name]) {
                $organization->holidays()->updateOrCreate(
                    ['date' => $date->toDateString()],
                    ['name' => $name, 'recurring' => true]
                );
            }
            foreach ($years as $year) {
                foreach ($this->floatingHolidaysForYear($year) as [$date, $name]) {
                    $organization->holidays()->updateOrCreate(
                        ['date' => $date->toDateString()],
                        ['name' => $name, 'recurring' => true]
                    );
                }
            }
        }
    }
}
