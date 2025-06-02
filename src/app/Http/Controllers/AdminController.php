<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Work;
use App\Models\Rest;
use App\Models\WorkRequest;
use App\Models\WorkRequestTime;
use Illuminate\Support\Carbon;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $thisDate = $request->input('date')
        ? Carbon::createFromFormat('Y-m-d', $request->input('date'))->startOfDay()
        : now()->startOfDay();

        $previousDay = $thisDate->copy()->subDay()->format('Y-m-d');
        $nextDay = $thisDate->copy()->addDay()->format('Y-m-d');

        $works = Work::with('user', 'rests')
        ->whereDate('date', $thisDate)
        ->orderBy('date', 'asc')->get();
        return view('admin.attendance_list', compact('thisDate', 'previousDay', 'nextDay', 'works'));
    }

    public function staff()
    {
        $users = User::all();
        return view('admin.staff_list', compact('users'));
    }

    public function private(Request $request, $userId)
    {
        $user = User::with('works')
        ->where('id', $userId)
        ->first();

        $inputMonth = $request->input('date');
        $thisMonth = $inputMonth
        ? Carbon::parse($inputMonth)->startOfMonth()
        : now()->startOfMonth();

        $previousMonth = $thisMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $thisMonth->copy()->addMonth()->format('Y-m');

        $works = Work::with('rests')
        ->whereBetween('date', [
            $thisMonth->copy()->startOfMonth(),
            $thisMonth->copy()->endOfMonth(),
        ])
        ->orderBy('date', 'asc')->get();
        return view('admin.private_list', compact('user', 'thisMonth', 'previousMonth', 'nextMonth', 'works'));
    }

    public function approve($workRequestId)
    {
        $workRequest = WorkRequest::with('user', 'work', 'times')
        ->where('id', $workRequestId)
        ->first();
        return view('admin.approval');
    }
}
