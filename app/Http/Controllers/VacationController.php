<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\HolidayYear;
use App\Models\User;
use App\Models\Presence;
use App\Models\Vacation;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class VacationController extends Controller
{
    public function index()
    {
        return view('vacations');
    }

    public function getUserVacations(Request $request)
    {
        $this->ensureHolidayYearsSynced();
        $user = Auth::user();
        $vacations = Vacation::where('user_id', $user->id)->orderBy('start_date')->get();
        $workingTime = $user->working_time;
        $vacationDaysByYear = $this->countVacationDaysByYearForUser($user->id);
        $holidayDaysByYear = $this->getHolidayDaysByYear();
        $currentYear = Carbon::now()->year;
        $countVacationDays = $vacationDaysByYear[$currentYear] ?? 0;

        $tenure = round($user->calculateTenureYears(), 1);
        $entitlement = $user->getVacationEntitlement();
        $hasEmploymentData = (bool) $user->employment_start_date;
        $isAdmin = (bool) $user->is_admin;

        $carryover = $this->calculateCarryover($user->id, $vacationDaysByYear, $entitlement, $hasEmploymentData);
        $saturdayReplacements = $this->countSaturdayReplacementsByYear($user->id);

        return response()->json(compact(
            'vacations', 'workingTime', 'vacationDaysByYear',
            'holidayDaysByYear', 'countVacationDays',
            'tenure', 'entitlement', 'hasEmploymentData', 'isAdmin', 'carryover',
            'saturdayReplacements'
        ));
    }

    public function getAdminVacations(Request $request)
    {
        if (!Auth::user()->is_admin) {
            return response()->json(['error' => 'Brak uprawnień'], 403);
        }

        $this->ensureHolidayYearsSynced();
        $year = $request->input('year', Carbon::now()->year);

        $users = User::orderBy('order')->get();

        $vacations = Vacation::where(function ($q) use ($year) {
            $q->where('start_date', '<=', $year . '-12-31')
              ->where('end_date', '>=', $year . '-01-01');
        })->orderBy('start_date')->get();

        $holidays = $this->getHolidays($year, $year);

        $entitlements = [];
        $saturdayReplacements = [];
        foreach ($users as $user) {
            $usedDays = $this->countVacationDaysByYearForUser($user->id, $year - 1, $year);
            $ent = $user->getVacationEntitlement();
            $hasData = (bool) $user->employment_start_date;
            $prevYearUsed = $usedDays[$year - 1] ?? 0;
            $carryoverFromPrev = $hasData ? max(0, $ent - $prevYearUsed) : null;
            $satRepl = $this->countSaturdayReplacementsByYear($user->id, $year, $year);

            $entitlements[$user->id] = [
                'tenure' => round($user->calculateTenureYears(), 1),
                'total_days' => $ent,
                'on_demand' => 4,
                'used' => $usedDays[$year] ?? 0,
                'remaining' => $ent - ($usedDays[$year] ?? 0),
                'carryover_from_prev' => $carryoverFromPrev,
                'has_employment_data' => $hasData,
                'saturday_used' => $satRepl[$year] ?? 0,
            ];
            $saturdayReplacements[$user->id] = $satRepl;
        }

        $holidayYear = HolidayYear::where('year', $year)->first();
        $saturdayHolidayCount = $holidayYear ? $holidayYear->saturday_holiday_count : 0;
        $saturdayHolidays = $this->getSaturdayHolidays($year);

        return response()->json(compact('users', 'vacations', 'holidays', 'entitlements', 'year', 'saturdayHolidayCount', 'saturdayHolidays'));
    }

    public function updateCarryover(Request $request)
    {
        if (!Auth::user()->is_admin) {
            return response()->json(['error' => 'Brak uprawnień'], 403);
        }

        $vacation = Vacation::find($request->vacationId);
        if (!$vacation) {
            return response()->json(['error' => 'Nie znaleziono urlopu'], 404);
        }

        $vacation->carryover_from_year = $request->carryover_from_year;
        $vacation->save();

        return response()->json(compact('vacation'));
    }

    public function addVacation(Request $request)
    {
        $errors = [];
        $vacation = new Vacation();
        $type = $request->type ?? 'vacation';

        if ($type === 'saturday_replacement') {
            if (!$request->saturdayHolidayDate) {
                $errors[] = __('Wybierz święto w sobotę, za które odbierasz dzień wolny.');
            } elseif ($request->startDate !== $request->endDate) {
                $errors[] = __('Odbiór za sobotę może obejmować tylko jeden dzień.');
            } else {
                $existing = Vacation::where('user_id', Auth::user()->id)
                    ->where('type', 'saturday_replacement')
                    ->where('saturday_holiday_date', $request->saturdayHolidayDate)
                    ->first();
                if ($existing) {
                    $errors[] = __('Dzień wolny za to święto został już odebrany.');
                }
            }
        }

        if (empty($errors)) {
            if ($request->startDate <= $request->endDate) {
                $vacations = Vacation::where('user_id', Auth::user()->id)->where('start_date', '<=', $request->endDate)->where('end_date', '>=', $request->startDate)->get();
                if ($request->requestDate > $request->startDate) {
                    $errors[] = __('Data złożenia wniosku nie może być późniejsza niż data rozpoczęcia urlopu.');
                } else if ($request->workingTime > 24 || $request->workingTime < 1) {
                    $errors[] = __('Nie można pracować więcej niż 24 godziny na dobę, ani krócej niż godzinę:)');
                } else {
                    if ($vacations->count() == 0) {
                        $presences = Presence::where('user_id', Auth::user()->id)
                        ->whereBetween('date', [$request->startDate, $request->endDate])
                        ->get();
                        if ($presences->isNotEmpty()) {
                            foreach ($presences as $presence) {
                                $presence->delete();
                            }
                        }
                        $vacation->user_id = Auth::user()->id;
                        $vacation->request_date = $request->requestDate;
                        $vacation->start_date = $request->startDate;
                        $vacation->end_date = $request->endDate;
                        $vacation->working_time = $request->workingTime;
                        $vacation->type = $type;
                        $vacation->saturday_holiday_date = $type === 'saturday_replacement' ? $request->saturdayHolidayDate : null;
                        $vacation->save();
                    } else {
                        $errors[] = __('Istnieje juz urlop w podanym terminie.');
                    }
                }
            } else {
                $errors[] = __('Data końca urlopu nie może być wcześniejsza niż data początku urlopu.');
            }
        }

        $vacations = Vacation::where('user_id', Auth::user()->id)->orderBy('start_date')->get();
        $vacationDaysByYear = $this->countVacationDaysByYearForUser(Auth::user()->id);
        $holidayDaysByYear = $this->getHolidayDaysByYear();
        $saturdayReplacements = $this->countSaturdayReplacementsByYear(Auth::user()->id);
        $currentYear = Carbon::now()->year;
        $countVacationDays = $vacationDaysByYear[$currentYear] ?? 0;
        return response()->json(compact('vacations', 'errors', 'vacationDaysByYear', 'holidayDaysByYear', 'countVacationDays', 'saturdayReplacements'));
    }

    public function getAllUsersVacations(Request $request)
    {
        $vacations = Vacation::where('start_date', '<=', $request->year . '-' . $request->month . '-31')->where('end_date', '>=', $request->year . '-' . $request->month . '-01')->orderBy('start_date')->get();
        $usersVacations = [];
        foreach ($vacations as $vacation) {
            $startDay = $vacation->start_date < Carbon::parse($request->year . '-' . $request->month . '-01') ? 1 : intval(date('d', strtotime($vacation->start_date)));
            $endDay = $vacation->end_date > Carbon::parse($request->year . '-' . $request->month . '-01')->endOfMonth() ? intval(date('t', strtotime($request->year . '-' . $request->month . '-01'))) : intval(date('d', strtotime($vacation->end_date)));
            for ($i = $startDay; $i <= $endDay; $i++) {
                $usersVacations[$vacation->user_id][] = $i;
            }
        }
        return response()->json( compact('usersVacations'));
    }

    public function getVacationCard(Request $request)
    {
        $vacation = Vacation::find($request->id);
        $user = User::find($vacation->user_id);
        $startDate = Carbon::parse($vacation->start_date);
        $endDate = Carbon::parse($vacation->end_date);
        $requestDate = Carbon::parse($vacation->request_date);
        $holidays = $this->getHolidays($startDate->year, $endDate->year);
        $workingDays = $this->countWorkingDays($startDate, $endDate, $holidays);
        $daysOff = (int) ($vacation->days_off ?? 0);
        $vacationDays = $workingDays - $daysOff;
        $vacationHours = $vacationDays * $vacation->working_time;
        $data = [
            'vacation' => $vacation,
            'user' => $user,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'workingDays' => $workingDays,
            'vacationDays' => $vacationDays,
            'vacationHours' => $vacationHours,
            'requestDate' => $requestDate
        ];
        $pdf = Pdf::loadView('vacationCard', $data);
        return $pdf->stream('pdf_file.pdf');
    }

    private function calculateCarryover(int $userId, array $vacationDaysByYear, int $entitlement, bool $hasEmploymentData): array
    {
        if (!$hasEmploymentData) {
            return [];
        }

        $carryover = [];
        $years = array_keys($vacationDaysByYear);
        sort($years);

        foreach ($years as $i => $year) {
            if ($i === 0) continue;
            $prevYear = $years[$i - 1];
            if ($prevYear !== $year - 1) continue;

            $prevUsed = $vacationDaysByYear[$prevYear] ?? 0;
            $remaining = max(0, $entitlement - $prevUsed);
            if ($remaining > 0) {
                $carryover[$year] = [
                    'from_year' => $prevYear,
                    'days' => $remaining,
                ];
            }
        }

        return $carryover;
    }

    private function countWorkingDays($startDate, $endDate, $holidays)
    {
        $begin = new \DateTime($startDate);
        $end = new \DateTime($endDate);
        if ($begin == $end) {
            if ($begin->format("N") < 6 && !in_array($begin->format("Y-m-d"), $holidays)) {
                return 1;
            } else {
                return 0;
            }
        }
        $interval = \DateInterval::createFromDateString('1 day');
        $period = new \DatePeriod($begin, $interval, $end->modify('+1 day'));
        $count = 0;
        foreach ($period as $date) {
            if ($date->format("N") < 6 && !in_array($date->format("Y-m-d"), $holidays)) {
                $count++;
            }
        }
        return $count;
    }

    private function getHolidays($startYear, $endYear)
    {
        $holidays = [];
        $years = range($startYear, $endYear);
        $storedYears = HolidayYear::whereIn('year', $years)->get()->keyBy('year');
        foreach ($years as $year) {
            if (isset($storedYears[$year]) && is_array($storedYears[$year]->holidays)) {
                $holidays = array_merge($holidays, $storedYears[$year]->holidays);
                continue;
            }
            $holidaysApi = "https://date.nager.at/api/v3/PublicHolidays/$year/PL";
            try {
                $holidaysData = file_get_contents($holidaysApi);
                if (false === $holidaysData) {
                    throw new \Exception("Błąd podczas pobierania danych z API dla roku $year");
                }
                $holidaysArray = json_decode($holidaysData, true);
                foreach ($holidaysArray as $holiday) {
                    $holidays[] = $holiday['date'];
                }
            } catch (\Exception $e) {
                // silently continue
            }
        }
        return array_values(array_unique($holidays));
    }

    private function countVacationDaysByYearForUser(int $userId, int $startYear = 2024, ?int $endYear = null)
    {
        $currentYear = $endYear ?? Carbon::now()->year;
        if ($currentYear < $startYear) {
            $startYear = $currentYear;
        }

        $daysByYear = [];
        for ($year = $startYear; $year <= $currentYear; $year++) {
            $daysByYear[$year] = 0;
        }

        $rangeStart = Carbon::create($startYear, 1, 1);
        $rangeEnd = Carbon::create($currentYear, 12, 31);
        $vacations = Vacation::where('user_id', $userId)
            ->where('type', 'vacation')
            ->where('end_date', '>=', $rangeStart->toDateString())
            ->where('start_date', '<=', $rangeEnd->toDateString())
            ->orderBy('start_date')
            ->get();

        if ($vacations->isEmpty()) {
            return $daysByYear;
        }

        $holidays = $this->getHolidays($startYear, $currentYear);

        foreach ($vacations as $vacation) {
            $vacationStart = Carbon::parse($vacation->start_date);
            $vacationEnd = Carbon::parse($vacation->end_date);
            $segmentStart = $vacationStart->lt($rangeStart) ? $rangeStart->copy() : $vacationStart->copy();
            $segmentEnd = $vacationEnd->gt($rangeEnd) ? $rangeEnd->copy() : $vacationEnd->copy();

            if ($segmentStart->gt($segmentEnd)) {
                continue;
            }

            $segments = [];
            $totalWorkingDays = 0;
            for ($year = $segmentStart->year; $year <= $segmentEnd->year; $year++) {
                $yearStart = Carbon::create($year, 1, 1);
                $yearEnd = Carbon::create($year, 12, 31);
                $segmentYearStart = $segmentStart->gt($yearStart) ? $segmentStart->copy() : $yearStart;
                $segmentYearEnd = $segmentEnd->lt($yearEnd) ? $segmentEnd->copy() : $yearEnd;

                if ($segmentYearStart->gt($segmentYearEnd)) {
                    continue;
                }

                $workingDays = $this->countWorkingDays(
                    $segmentYearStart->toDateString(),
                    $segmentYearEnd->toDateString(),
                    $holidays
                );
                $segments[$year] = $workingDays;
                $totalWorkingDays += $workingDays;
            }

            if ($totalWorkingDays === 0) {
                continue;
            }

            $daysOff = (int) ($vacation->days_off ?? 0);
            if ($daysOff > 0) {
                $remainingDaysOff = $daysOff;
                $segmentYears = array_keys($segments);
                $lastYear = end($segmentYears);
                foreach ($segments as $year => $workingDays) {
                    if ($year === $lastYear) {
                        $allocated = $remainingDaysOff;
                    } else {
                        $allocated = (int) floor(($workingDays / $totalWorkingDays) * $daysOff);
                        $remainingDaysOff -= $allocated;
                    }
                    $segments[$year] = max(0, $workingDays - $allocated);
                }
            }

            foreach ($segments as $year => $vacationDays) {
                if (array_key_exists($year, $daysByYear)) {
                    $daysByYear[$year] += $vacationDays;
                }
            }
        }

        return $daysByYear;
    }

    private function getHolidayDaysByYear(int $startYear = 2024)
    {
        $currentYear = Carbon::now()->year;
        if ($currentYear < $startYear) {
            $startYear = $currentYear;
        }

        $years = range($startYear, $currentYear);
        $holidayDaysByYear = array_fill_keys($years, null);

        $holidayYears = HolidayYear::whereIn('year', $years)->get(['year', 'saturday_holiday_count']);
        foreach ($holidayYears as $holidayYear) {
            $holidayDaysByYear[$holidayYear->year] = $holidayYear->saturday_holiday_count;
        }

        return $holidayDaysByYear;
    }

    public function deleteVacation(Request $request)
    {
        $vacation = Vacation::find($request->vacationId);
        $vacation->delete();
        $vacations = Vacation::where('user_id', Auth::user()->id)->orderBy('start_date')->get();
        $vacationDaysByYear = $this->countVacationDaysByYearForUser(Auth::user()->id);
        $holidayDaysByYear = $this->getHolidayDaysByYear();
        $saturdayReplacements = $this->countSaturdayReplacementsByYear(Auth::user()->id);
        $currentYear = Carbon::now()->year;
        $countVacationDays = $vacationDaysByYear[$currentYear] ?? 0;
        return response()->json(compact('vacations', 'vacationDaysByYear', 'holidayDaysByYear', 'countVacationDays', 'saturdayReplacements'));
    }

    public function getSaturdayHolidaysForYear(Request $request)
    {
        $year = $request->input('year', Carbon::now()->year);
        $saturdayHolidays = $this->getSaturdayHolidays($year);
        return response()->json(compact('saturdayHolidays', 'year'));
    }

    private function countSaturdayReplacementsByYear(int $userId, ?int $startYear = null, ?int $endYear = null): array
    {
        $startYear = $startYear ?? 2024;
        $endYear = $endYear ?? Carbon::now()->year;

        $byYear = [];
        for ($y = $startYear; $y <= $endYear; $y++) {
            $byYear[$y] = 0;
        }

        $replacements = Vacation::where('user_id', $userId)
            ->where('type', 'saturday_replacement')
            ->whereYear('start_date', '>=', $startYear)
            ->whereYear('start_date', '<=', $endYear)
            ->get();

        foreach ($replacements as $r) {
            $year = Carbon::parse($r->start_date)->year;
            if (array_key_exists($year, $byYear)) {
                $byYear[$year]++;
            }
        }

        return $byYear;
    }

    private function ensureHolidayYearsSynced(): void
    {
        $currentYear = Carbon::now()->year;
        $years = [$currentYear, $currentYear + 1];

        foreach ($years as $year) {
            if (HolidayYear::where('year', $year)->exists()) {
                continue;
            }
            try {
                $holidaysApi = "https://date.nager.at/api/v3/PublicHolidays/$year/PL";
                $holidaysData = @file_get_contents($holidaysApi);
                if (false === $holidaysData) continue;

                $holidaysArray = json_decode($holidaysData, true);
                if (!is_array($holidaysArray)) continue;

                $dates = array_values(array_unique(array_column($holidaysArray, 'date')));
                $saturdayCount = 0;
                foreach ($dates as $date) {
                    if (Carbon::parse($date)->isSaturday()) $saturdayCount++;
                }

                HolidayYear::create([
                    'year' => $year,
                    'holidays' => $dates,
                    'saturday_holiday_count' => $saturdayCount,
                    'synced_at' => Carbon::now(),
                ]);
            } catch (\Throwable $e) {
                // API niedostępne — pomiń
            }
        }
    }

    private function getSaturdayHolidays(int $year): array
    {
        $holidayYear = HolidayYear::where('year', $year)->first();
        if (!$holidayYear || !is_array($holidayYear->holidays)) {
            return [];
        }

        $saturdayHolidays = [];
        foreach ($holidayYear->holidays as $dateStr) {
            $date = Carbon::parse($dateStr);
            if ($date->isSaturday()) {
                $saturdayHolidays[] = $dateStr;
            }
        }

        return $saturdayHolidays;
    }
}
