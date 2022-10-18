<?php

namespace App\Http\Controllers;

use App\Http\Resources\StatisticResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StatisticController extends Controller
{
    public function getMyStatistic()
    {
        return new StatisticResource(Auth::user()->statistic);
    }
}
