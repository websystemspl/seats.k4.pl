<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\HolidayYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HolidayYearController extends Controller
{
    public function index()
    {
        if (!Auth::user() || !Auth::user()->is_admin) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $holidayYears = HolidayYear::orderBy('year', 'desc')
            ->get(['year', 'saturday_holiday_count', 'synced_at']);

        return response()->json(compact('holidayYears'));
    }

    public function sync(Request $request)
    {
        if (!Auth::user() || !Auth::user()->is_admin) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'year' => 'required|integer|min:1970|max:2100'
        ]);

        $year = (int) $request->year;

        try {
            $holidays = $this->fetchHolidaysForYear($year);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Nie udało się pobrać świąt. Spróbuj ponownie.'], 500);
        }

        $holidayDates = array_values(array_unique(array_map(function ($holiday) {
            return $holiday['date'];
        }, $holidays)));

        $saturdayCount = 0;
        foreach ($holidayDates as $date) {
            if (Carbon::parse($date)->isSaturday()) {
                $saturdayCount++;
            }
        }

        $holidayYear = HolidayYear::updateOrCreate(
            ['year' => $year],
            [
                'holidays' => $holidayDates,
                'saturday_holiday_count' => $saturdayCount,
                'synced_at' => Carbon::now()
            ]
        );

        return response()->json(['holidayYear' => $holidayYear]);
    }

    private function fetchHolidaysForYear(int $year)
    {
        $holidaysApi = "https://date.nager.at/api/v3/PublicHolidays/$year/PL";
        $holidaysData = file_get_contents($holidaysApi);
        if (false === $holidaysData) {
            throw new \RuntimeException("Błąd podczas pobierania danych z API dla roku $year");
        }
        $holidaysArray = json_decode($holidaysData, true);
        if (!is_array($holidaysArray)) {
            throw new \RuntimeException("Niepoprawny format danych dla roku $year");
        }
        return $holidaysArray;
    }
}
