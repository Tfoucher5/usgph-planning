<?php

namespace Database\Seeders;

use App\Models\Planning\Planning;
use App\Models\Planning\Tache;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanningSeeder extends Seeder
{
    private const MIN_WEEKLY_MINUTES = 35 * 60;  // 35 heures minimum

    private const MAX_WEEKLY_MINUTES = 45 * 60;  // 45 heures maximum

    private const MAX_DAILY_MINUTES = 10 * 60;   // 10 heures maximum

    private const MIN_DAILY_MINUTES = 6 * 60;    // 6 heures minimum par jour

    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $endDate = Carbon::now()->subWeek()->endOfWeek();
        $startDate = Carbon::now()->subYear()->subWeek()->startOfWeek();
        $users = [2, 3, 4];

        $tasksByUserAndDay = $this->preloadTasks($users);

        while ($startDate <= $endDate) {
            $plannings = [];
            foreach ($users as $userId) {
                $weeklyPlannings = $this->generateRealisticWeek(
                    $userId,
                    $startDate->copy(),
                    $tasksByUserAndDay[$userId] ?? []
                );
                $plannings = array_merge($plannings, $weeklyPlannings);
            }

            if (!empty($plannings)) {
                Planning::insert($plannings);
            }

            $startDate->addWeek();
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    private function preloadTasks($users)
    {
        $tasks = Tache::whereIn('user_id', $users)->get();
        $tasksByUserAndDay = [];

        foreach ($tasks as $task) {
            if ($task->jour !== 0) {
                $tasksByUserAndDay[$task->user_id][$task->jour][] = $task;
            }
        }

        return $tasksByUserAndDay;
    }

    private function generateRealisticWeek($userId, Carbon $weekStart, array $userTasks)
    {
        $plannings = [];
        $weeklyMinutes = 0;

        for ($day = 1; $day <= 6; $day++) {
            $dailyMinutes = 0;
            $taskDate = $weekStart->copy()->addDays($day - 1);

            if ($weeklyMinutes >= self::MAX_WEEKLY_MINUTES) {
                break;
            }

            $availableTasks = $userTasks[$day] ?? [];
            shuffle($availableTasks);

            $remainingNeededMinutes = self::MIN_WEEKLY_MINUTES - $weeklyMinutes;
            $remainingDays = 6 - $day + 1;
            $targetDailyMinutes = $remainingNeededMinutes > 0
                ? $this->roundToQuarter(ceil($remainingNeededMinutes / $remainingDays))
                : self::MIN_DAILY_MINUTES;

            foreach ($availableTasks as $tache) {
                $startTime = Carbon::parse($tache->heure_debut);
                $endTime = Carbon::parse($tache->heure_fin);
                $taskMinutes = $this->roundToQuarter($endTime->diffInMinutes($startTime));

                if ($dailyMinutes + $taskMinutes > self::MAX_DAILY_MINUTES ||
                    $weeklyMinutes + $taskMinutes > self::MAX_WEEKLY_MINUTES) {
                    continue;
                }

                $hasOverlap = false;
                foreach ($plannings as $planning) {
                    if ($planning['plannifier_le'] === $taskDate->format('Y-m-d') &&
                        $this->timesOverlap(
                            $planning['heure_debut'],
                            $planning['heure_fin'],
                            $tache->heure_debut,
                            $tache->heure_fin
                        )) {
                        $hasOverlap = true;
                        break;
                    }
                }

                if ($hasOverlap) {
                    continue;
                }

                $shouldAdd = $dailyMinutes < $targetDailyMinutes
                    || ($weeklyMinutes < self::MIN_WEEKLY_MINUTES && rand(1, 100) <= 70)
                    || rand(1, 100) <= 30;

                if ($shouldAdd) {
                    $plannings[] = [
                        'user_id' => $userId,
                        'lieu_id' => $tache->lieu_id,
                        'nom' => $tache->nom,
                        'plannifier_le' => $taskDate->format('Y-m-d'),
                        'heure_debut' => $tache->heure_debut,
                        'heure_fin' => $tache->heure_fin,
                        'is_validated' => $taskDate->lt(Carbon::now()),
                        'user_id_creation' => 5,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    $dailyMinutes += $taskMinutes;
                    $weeklyMinutes += $taskMinutes;
                }
            }
        }

        return $plannings;
    }

    private function timesOverlap($start1, $end1, $start2, $end2)
    {
        return $start1 < $end2 && $end1 > $start2;
    }

    private function roundToQuarter($minutes)
    {
        return round($minutes / 15) * 15;
    }
}
