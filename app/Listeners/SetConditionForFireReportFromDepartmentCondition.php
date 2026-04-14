<?php

namespace App\Listeners;

use App\Condition;
use App\DepartmentReportCondition;
use App\Events\FireReportUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SetConditionForFireReportFromDepartmentCondition
{
    public function handle(FireReportUpdated $event): void
    {
        $fireReport = $event->fireReport;
        $departments = $fireReport->departments;
        $maxDepartmentConditionId = DepartmentReportCondition::tryFrom($departments->max('pivot.condition_id'));
        if ($maxDepartmentConditionId) {
            switch ($maxDepartmentConditionId) {
                case DepartmentReportCondition::CREATED:
                case DepartmentReportCondition::ACCEPTED:
                    $fireReport->condition = Condition::RESPONSE;
                    break;
                case DepartmentReportCondition::ARRIVED:
                    $fireReport->condition = Condition::AT_WORK;
                    break;
                case DepartmentReportCondition::DONE:
                    $fireReport->condition = Condition::DONE;
                    break;
            }
            $fireReport->save();
        }
    }
}
