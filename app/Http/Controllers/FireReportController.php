<?php

namespace App\Http\Controllers;

use App\Models\FireReport;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class FireReportController extends Controller
{
    use AuthorizesRequests;
    public function setStatusConnected(int $id)
    {
        $fireReport = FireReport::findOrFail($id);
        $this->authorize('setStatusConnected', $fireReport);
        $fireReport->condition = 2;
        $fireReport->update();
    }

}
