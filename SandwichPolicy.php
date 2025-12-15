<?php

namespace App\Http\Controllers\Api\Register;

use App\Http\Controllers\Controller;
use App\Models\Detail;
use App\Models\Holiday;
use App\Models\LeaveApplication;
use App\Models\LeaveBalance;
use App\Models\SandwichPolicySetting;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        // Basic validation
        // $request->validate([
        //     'name' => 'required|string|max:255',
        //     'email' => 'required|email|unique:users',
        //     'password' => 'required|string|min:6',
        // ]);

        $user = User::create([
            'name' => $request->name,
            'mobile' => $request->mobile,
            // 'password' => Hash::make($request->password),
        ]);

        return response()->json(['message' => 'User Registered Successfully', 'user' => $user], 201);
    }

    public function getUser(Request $request)
    {
        $users = User::get();

        $data = [];

        foreach ($users as $key => $user) {
            $data[]  = [
                'name' => $user->name,
                'mobile' => $user->mobile,
            ];
        }

        return response()->json(['message' => 'User Get Successfully', 'user' => $data], 201);
    }

    public function addUser(Request $request)
    {
        $names = ['Vimal1', 'Nihar1'];

        $existingUser = Detail::whereIn('name', $names)->pluck('name')->toArray();

        if (!empty($existingUser)) {
            $disName = implode(", ", $existingUser);
            return response()->json([
                'message' => 'Users Alredy Taken into DB',
                'user' => $disName,
            ], 404);
        }

        // Create users that don't exist
        $createdUsers = [];
        foreach ($names as $name) {
            $createdUsers[] = Detail::create([
                'name' => $name,
            ]);
        }

        return response()->json([
            'message' => 'Users stored as JSON',
            'users' => $createdUsers,
        ], 201);
    }

    public function applyLeave123(Request $request)
    {
        // Validate request
        $request->validate([
            'employee_id'   => 'required|integer',
            'leave_type_id' => 'required|integer',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after_or_equal:start_date',
            'total_days'    => 'required|numeric|min:1',
            'status'        => 'required|string',
            'applied_on'    => 'required|date',
            'approver_id'   => 'required|integer'
        ]);

        $employeeId  = $request->employee_id;
        $leaveTypeId = $request->leave_type_id;

        // Get leave type
        $leaveType = LeaveType::where('leave_type_id', $leaveTypeId)->first();

        if (!$leaveType) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid leave type.'
            ], 404);
        }

        // If Casual Leave, check balance
        if ($leaveType->name === 'Casual Leave') {

            $balance = LeaveBalance::where('employee_id', $employeeId)
                ->where('leave_type_id', $leaveTypeId)
                ->first();

            if (!$balance) {
                return response()->json([
                    'status' => false,
                    'message' => 'Leave balance not found.'
                ], 404);
            }

            if ($balance->remaining < $request->total_days) {
                return response()->json([
                    'status' => false,
                    'message' => 'Insufficient leave balance.'
                ], 400);
            }

            // Update balance
            $balance->pending   += $request->total_days;
            $balance->remaining -= $request->total_days;
            $balance->save();
        }

        // Apply Leave (Common for all leave types)
        LeaveApplication::create([
            'employee_id'   => $employeeId,
            'leave_type_id' => $leaveTypeId,
            'start_date'    => $request->start_date,
            'end_date'      => $request->end_date,
            'total_days'    => $request->total_days,
            'status'        => $request->status,
            'applied_on'    => $request->applied_on,
            'approver_id'   => $request->approver_id
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Leave applied successfully.'
        ], 201);
    }

    public function SaveSandwichPolicy(Request $request)
    {
        // 1️⃣ Get the first record or create a new one
        $setting = SandwichPolicySetting::first();
        if (!$setting) {
            $setting = new SandwichPolicySetting();
        }

        // 2️⃣ Assign values
        $setting->is_enabled = $request->boolean('is_enabled');
        $setting->apply_on_weekly_off = $request->boolean('apply_on_weekly_off');
        $setting->apply_on_holiday = $request->boolean('apply_on_holiday');
        $setting->disable_from = $request->disable_from;
        $setting->disable_to = $request->disable_to;

        // 3️⃣ Save (Insert or Update)
        $setting->save();

        // 4️⃣ Return JSON response
        return response()->json([
            'status' => true,
            'message' => 'Sandwich Policy updated successfully',
            'data' => $setting
        ]);
    }

    private function calculateLeaveDays($startDate, $endDate)
    {
        $start = Carbon::parse($startDate);
        $end   = Carbon::parse($endDate);
        $period = CarbonPeriod::create($start, $end);
        $days = 0;

        // Get current policy
        $policy = SandwichPolicySetting::first();
        $applyWeeklyOff = $policy ? $policy->apply_on_weekly_off : true;
        $applyHoliday   = $policy ? $policy->apply_on_holiday : true;
        $disableFrom    = $policy ? $policy->disable_from : null;
        $disableTo      = $policy ? $policy->disable_to : null;

        foreach ($period as $date) {

            // Check if this date falls in the disabled range
            if ($disableFrom && $disableTo && $date->between($disableFrom, $disableTo)) {
                // Count only working days (ignore weekly off & holidays)
                if ($date->isWeekend()) continue;
                if (Holiday::whereDate('holiday_date', $date)->exists()) continue;
                $days++;
                continue;
            }

            // Sandwich Policy active: Check Weekly Off / Holiday
            if ($date->isWeekend() && !$applyWeeklyOff) continue;
            if (Holiday::whereDate('holiday_date', $date)->exists() && !$applyHoliday) continue;

            $days++;
        }

        return $days;
    }

    private function calculateLeaveDaysWithAdminControl($startDate, $endDate)
    {
        $start = Carbon::parse($startDate);
        $end   = Carbon::parse($endDate);

        $setting = SandwichPolicySetting::first();

        $period = CarbonPeriod::create($start, $end);
        $days = 0;

        foreach ($period as $date) {

            // ============================
            // Sandwich Policy DISABLED
            // ============================
            if (
                !$setting->is_enabled ||
                (
                    $setting->disable_from &&
                    $setting->disable_to &&
                    $date->between($setting->disable_from, $setting->disable_to)
                )
            ) {
                // Count only working days
                if ($date->isWeekend()) continue;
                if (Holiday::whereDate('holiday_date', $date)->exists()) continue;

                $days++;
                continue;
            }

            // ============================
            // Sandwich Policy ENABLED
            // ============================
            if ($date->isWeekend() && !$setting->apply_on_weekly_off) {
                continue;
            }

            if (
                Holiday::whereDate('holiday_date', $date)->exists() &&
                !$setting->apply_on_holiday
            ) {
                continue;
            }

            $days++;
        }

        return $days;
    }


    public function applyLeave(Request $request)
    {
        $request->validate([
            'employee_id'   => 'required|integer',
            'leave_type_id' => 'required|integer',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after_or_equal:start_date',
            'status'        => 'required|string',
            'applied_on'    => 'required|date',
            'approver_id'   => 'required|integer',
        ]);

        $startDate = Carbon::parse($request->start_date);
        $endDate   = Carbon::parse($request->end_date);

        // ===============================
        // Sandwich policy
        // ===============================
        $policy = DB::table('sandwich_policy_settings')->first();
        $disableFrom = Carbon::parse($policy->disable_from);
        $disableTo   = Carbon::parse($policy->disable_to);

        // ===============================
        // Holidays
        // ===============================
        $holidays = DB::table('holidays')
            ->pluck('holiday_date')
            ->map(fn($d) => Carbon::parse($d)->toDateString())
            ->toArray();

        $totalDays = 0;

        // ===============================
        // 1️⃣ Count leave days
        // ===============================
        foreach (CarbonPeriod::create($startDate, $endDate) as $date) {

            // Disable window → count everything
            if ($date->between($disableFrom, $disableTo)) {
                $totalDays++;
                continue;
            }

            // Skip holiday
            if (in_array($date->toDateString(), $holidays)) {
                continue;
            }

            // Skip weekly off (Sunday)
            if ($date->isSunday()) {
                continue;
            }

            $totalDays++;
        }

        // ===============================
        // 2️⃣ Sandwich (ONLY single-day leave)
        // ===============================
        $isSingleDayLeave = $startDate->equalTo($endDate);

        if (
            $policy->is_enabled == 1 &&
            $isSingleDayLeave &&
            !$startDate->between($disableFrom, $disableTo)
        ) {
            $previousDay = $startDate->copy()->subDay();

            if (
                in_array($previousDay->toDateString(), $holidays) &&
                !$previousDay->between($disableFrom, $disableTo)
            ) {
                $totalDays++; // add ONLY ONE holiday
            }
        }

        // ===============================
        // 3️⃣ Leave balance
        // ===============================
        $balance = LeaveBalance::where('employee_id', $request->employee_id)
            ->where('leave_type_id', $request->leave_type_id)
            ->first();

        if (!$balance || $balance->remaining < $totalDays) {
            return response()->json([
                'status' => false,
                'message' => 'Insufficient leave balance'
            ], 400);
        }

        $balance->remaining -= $totalDays;
        $balance->pending   += $totalDays;
        $balance->save();

        // ===============================
        // 4️⃣ Save leave
        // ===============================
        LeaveApplication::create([
            'employee_id'   => $request->employee_id,
            'leave_type_id' => $request->leave_type_id,
            'start_date'    => $startDate,
            'end_date'      => $endDate,
            'total_days'    => $totalDays,
            'status'        => $request->status,
            'applied_on'    => $request->applied_on,
            'approver_id'   => $request->approver_id
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Leave applied successfully',
            'leave_days' => $totalDays,
            'remaining_balance' => $balance->remaining
        ]);
    }


    public function applyLeave15(Request $request)
    {
        // =====================
        // 1. Validation
        // =====================
        $request->validate([
            'employee_id'   => 'required|integer',
            'leave_type_id' => 'required|integer',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after_or_equal:start_date',
            'status'        => 'required|string',
            'applied_on'    => 'required|date',
            'approver_id'   => 'required|integer'
        ]);

        $employeeId  = $request->employee_id;
        $leaveTypeId = $request->leave_type_id;

        // =====================
        // 2. Get Leave Type
        // =====================
        $leaveType = LeaveType::where('leave_type_id', $leaveTypeId)->first();

        if (!$leaveType) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid leave type'
            ], 404);
        }

        // =====================
        // 3. Calculate Leave Days (Sandwich Policy)
        // =====================

        // $totalLeaveDays = $this->calculateSandwichDays(
        //     $request->start_date,
        //     $request->end_date
        // );

        // $totalLeaveDays = $this->calculateLeaveDaysWithAdminControl(
        //     $request->start_date,
        //     $request->end_date
        // );

        $totalLeaveDays = $this->calculateLeaveDays(
            $request->start_date,
            $request->end_date
        );



        // =====================
        // 4. Balance Check (ONLY for Casual Leave)
        // =====================
        if ($leaveType->name === 'Casual Leave') {

            $balance = LeaveBalance::where('employee_id', $employeeId)
                ->where('leave_type_id', $leaveTypeId)
                ->first();

            if (!$balance) {
                return response()->json([
                    'status' => false,
                    'message' => 'Leave balance not found'
                ], 404);
            }

            // 👉 THIS IS THE CODE YOU ASKED ABOUT
            if ($balance->remaining < $totalLeaveDays) {
                return response()->json([
                    'status' => false,
                    'message' => 'Insufficient leave balance'
                ], 400);
            }

            // Update balance
            $balance->pending   += $totalLeaveDays;
            $balance->remaining -= $totalLeaveDays;
            $balance->save();
        }

        // =====================
        // 5. Save Leave Application
        // =====================
        LeaveApplication::create([
            'employee_id'   => $employeeId,
            'leave_type_id' => $leaveTypeId,
            'start_date'    => $request->start_date,
            'end_date'      => $request->end_date,
            'total_days'    => $totalLeaveDays,
            'status'        => $request->status,
            'applied_on'    => $request->applied_on,
            'approver_id'   => $request->approver_id
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Leave applied successfully',
            'total_days' => $totalLeaveDays
        ], 201);
    }

    // =====================
    // Sandwich Policy Logic
    // =====================
    private function calculateSandwichDays($startDate, $endDate)
    {
        $start = Carbon::parse($startDate);
        $end   = Carbon::parse($endDate);

        $period = CarbonPeriod::create($start, $end);

        $days = 0;

        foreach ($period as $date) {

            // Weekly Off
            if ($date->isSaturday() || $date->isSunday()) {
                $days++;
                continue;
            }

            // Holiday
            if (Holiday::whereDate('holiday_date', $date)->exists()) {
                $days++;
                continue;
            }

            // Working day
            $days++;
        }

        return $days;
    }

    // public function applyLeaveM(Request $request)
    // {
    //     $employeeId =  $request->employee_id;
    //     $leaveTypeId =  $request->leave_type_id;
    //     $leaveType = LeaveType::where('leave_type_id', $leaveTypeId)->first();

    //     if ($leaveType->name == 'Unpaid Leave') {
    //         $user = LeaveApplication::create([
    //             'employee_id' => $request->employee_id,
    //             'leave_type_id' => $request->leave_type_id,
    //             'start_date' => $request->start_date,
    //             'end_date' => $request->end_date,
    //             'total_days' => $request->total_days,
    //             'status' => $request->status,
    //             'applied_on' => $request->applied_on,
    //             'approver_id' => $request->approver_id
    //         ]);

    //         return response()->json(['message' => 'Leave Apply Successfully'], 201);
    //     }

    //     if ($leaveType->name == 'Casual Leave') {

    //         $balance = LeaveBalance::where('employee_id', $employeeId)
    //             ->where('leave_type_id', $leaveTypeId)
    //             ->first();

    //         if ($balance->remaining < $request->total_days) {
    //             return response()->json([
    //                 "status" => false,
    //                 "message" => "Insufficient leave balance."
    //             ], 400);
    //         }

    //         $user = LeaveApplication::create([
    //             'employee_id' => $request->employee_id,
    //             'leave_type_id' => $request->leave_type_id,
    //             'start_date' => $request->start_date,
    //             'end_date' => $request->end_date,
    //             'total_days' => $request->total_days,
    //             'status' => $request->status,
    //             'applied_on' => $request->applied_on,
    //             'approver_id' => $request->approver_id
    //         ]);

    //         $balance->pending += $request->total_days;
    //         $balance->remaining -= $request->total_days;
    //         $balance->save();

    //         return response()->json(['message' => 'Leave Apply Successfully'], 201);
    //     }
    // }
}
