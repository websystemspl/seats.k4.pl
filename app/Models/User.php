<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'working_time',
        'order',
        'employment_start_date',
        'education_years',
        'education_completed_date',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'employment_start_date' => 'date',
            'education_completed_date' => 'date',
        ];
    }

    public function vacations()
    {
        return $this->hasMany(Vacation::class);
    }

    public function presences()
    {
        return $this->hasMany(Presence::class);
    }

    public function employmentLogs()
    {
        return $this->hasMany(EmploymentLog::class);
    }

    public function documents()
    {
        return $this->hasMany(UserDocument::class);
    }

    public function calculateTenureYears(): float
    {
        $today = Carbon::now();
        $workYears = 0;
        $educationYears = (int) ($this->education_years ?? 0);

        if ($this->employment_start_date) {
            $workYears = Carbon::parse($this->employment_start_date)->floatDiffInYears($today);
        }

        if ($educationYears <= 0) {
            return max(0, $workYears);
        }

        if (!$this->employment_start_date) {
            return $educationYears;
        }

        if (!$this->education_completed_date) {
            return max($educationYears, $workYears);
        }

        $edCompleted = Carbon::parse($this->education_completed_date);
        $edStarted = $edCompleted->copy()->subYears($educationYears);
        $empStart = Carbon::parse($this->employment_start_date);

        $overlapEnd = $edCompleted->lt($today) ? $edCompleted : $today;
        $overlapStart = $edStarted->gt($empStart) ? $edStarted : $empStart;

        $overlap = 0;
        if ($overlapEnd->gt($overlapStart)) {
            $overlap = $overlapStart->floatDiffInYears($overlapEnd);
        }

        return max(0, $educationYears + $workYears - $overlap);
    }

    public function getVacationEntitlement(): int
    {
        return $this->calculateTenureYears() >= 10 ? 26 : 20;
    }
}
