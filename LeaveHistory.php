<?php

namespace App\Http\Controllers\Api\Register;

use App\Http\Controllers\Controller;
use App\Models\Detail;
use App\Models\LeaveAccrualLog;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

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

    public function calculateLeave(Request $request)
    {
        $start = Carbon::createFromFormat('d-m-Y', $request->start_date);
        $end   = Carbon::createFromFormat('d-m-Y', $request->end_date);

        $policy = DB::table('sandwich_policy_settings')->first();

        $leaveDays = 0;

        foreach (CarbonPeriod::create($start, $end) as $date) {

            $isWeekend = $date->isSaturday() || $date->isSunday();

            // Check sandwich disable period
            $sandwichDisabled =
                $policy->disable_from &&
                $policy->disable_to &&
                $date->between(
                    Carbon::parse($policy->disable_from),
                    Carbon::parse($policy->disable_to)
                );

            if ($sandwichDisabled) {
                // During disabled period, count only working days
                if (!$isWeekend) {
                    $leaveDays++;
                }
            } else {
                // Sandwich enabled
                if ($policy->apply_on_weekly_off || !$isWeekend) {
                    $leaveDays++;
                }
            }
        }

        return response()->json([
            'leave_days' => $leaveDays
        ]);
    }

    public function leaveHistory()
    {
        $userId = 1;
        $balance = 0;
        $data = [];

        /* Monthly accruals */
        $accruals = LeaveAccrualLog::where('user_id', $userId)
            ->orderBy('created_at')
            ->get();

        foreach ($accruals as $accrual) {
            $balance += 2;

            $data[] = [
                'Month' => $accrual->month,
                'transactionDate' => date('d-m-Y', strtotime($accrual->created_at)),
                'change' => '+2',
                'balance' => $balance,
                'reason' => 'Monthly Accrual'
            ];

            /* Leaves applied in this month */
            $leaves = DB::table('leave_applications')
                ->where('employee_id', $userId)
                ->where('status', 'Approved')
                ->whereMonth('created_at', date('m', strtotime($accrual->created_at)))
                ->whereYear('created_at', date('Y', strtotime($accrual->created_at)))
                ->get();

            foreach ($leaves as $leave) {
                $balance -= $leave->total_days;

                $data[] = [
                    'transactionDate' => date('d-m-Y', strtotime($leave->created_at)),
                    'change' => -$leave->total_days,
                    'balance' => $balance,
                    'reason' => 'Leave Apply from ' .
                        date('d-m-Y', strtotime($leave->start_date)) .
                        ' to ' .
                        date('d-m-Y', strtotime($leave->end_date))
                ];
            }
        }

        return response()->json([
            'message' => 'User Get Successfully',
            'user' => $data
        ], 201);
    }


    public function leaveHistoryM(Request $request)
    {
        $userId = 1;

        $balance = 0; // ledger always starts from 0
        $ledger = [];

        /* ========= ACCRUAL ========= */
        $leaveAccrualLog = LeaveAccrualLog::where('user_id', $userId)->get();

        foreach ($leaveAccrualLog as $val) {
            $ledger[] = [
                'date'   => $val->created_at,
                'type'   => 'accrual',
                'change' => 2,
                'month'  => $val->month
            ];
        }

        /* ========= LEAVE ========= */
        $leaveApplications = DB::table('leave_applications')
            ->where('employee_id', $userId)
            ->where('status', 'Approved')
            ->get();

        foreach ($leaveApplications as $val) {
            $ledger[] = [
                'date'   => $val->created_at,
                'type'   => 'leave',
                'change' => -$val->total_days,
                'start'  => $val->start_date,
                'end'    => $val->end_date
            ];
        }

        /* ========= SORT BY DATE ========= */
        usort($ledger, function ($a, $b) {
            return strtotime($a['date']) <=> strtotime($b['date']);
        });

        /* ========= FINAL OUTPUT ========= */
        $result = [];

        foreach ($ledger as $row) {
            $balance += $row['change'];

            if ($row['type'] === 'accrual') {
                $result[] = [
                    'Month'           => $row['month'],
                    'transactionDate' => date('d-m-Y', strtotime($row['date'])),
                    'change'          => '+2',
                    'balance'         => $balance,
                    'reason'          => 'Monthly Accrual'
                ];
            } else {
                $result[] = [
                    'transactionDate' => date('d-m-Y', strtotime($row['date'])),
                    'change'          => $row['change'],
                    'balance'         => $balance,
                    'reason'          => 'Leave Apply from ' .
                        date('d-m-Y', strtotime($row['start'])) .
                        ' to ' .
                        date('d-m-Y', strtotime($row['end']))
                ];
            }
        }

        return response()->json([
            'message' => 'User Get Successfully',
            'user'    => $result
        ], 200);
    }


    public function leaveHistory22(Request $request)
    {
        $userId = 1;

        $leaveAccrualLog = LeaveAccrualLog::where('user_id', $userId)->get();

        $remainingBalance = DB::table('leave_balances')->where('user_id', $userId)->value('allocated');

        $leaveApplications = DB::table('leave_applications')
            ->where('employee_id', $userId)
            ->where('status', 'Approved')
            ->get();
        $data1 = [];
        foreach ($leaveApplications as $key => $val) {
            $totalBal =  $remainingBalance -= $val->total_days;
            $startDate = date('d-m-Y', strtotime($val->start_date));
            $endDate = date('d-m-Y', strtotime($val->end_date));

            $data1[]  = [
                'transactionDate' => date('d-m-Y', strtotime($val->created_at)),
                'change' =>  $totalBal,
                'balance' => $remainingBalance,
                "reason"  => "Leave Apply from " . $startDate . " to " . $endDate . " as reason."
            ];
        }


        $data2 = [];

        $change = 2;
        $balance = 0;
        foreach ($leaveAccrualLog as $key => $val) {
            $totalBal =  $balance += $change;
            $data2[]  = [
                'Month' => $val->month,
                'transactionDate' => date('d-m-Y', strtotime($val->created_at)),
                'change' => '+' . $change,
                'balance' => $totalBal,
                "reason"  => "Monthly Accrual"
            ];
        }

        $result = array_merge($data1, $data2);


        return response()->json(['message' => 'User Get Successfully', 'user' => $result], 201);
    }

    public function leaveHistory11(Request $request)
    {
        //exit("ffffff");
        //$userId = auth()->id();
        $userId = 1;

        // Leave applications (negative)
        $leaveApplications = DB::table('leave_applications')
            ->where('employee_id', $userId)
            ->where('status', 'Approved')
            ->select(
                DB::raw('end_date as transaction_date'),
                DB::raw('(-1 * total_days) as change_days'),
                DB::raw("CONCAT('Leave Apply from ', start_date, ' to ', end_date) as reason")
            );

        // Monthly accruals (positive)
        $leaveAccruals = DB::table('leave_accrual_logs')
            ->where('user_id', $userId)
            ->select(
                DB::raw("LAST_DAY(CONCAT(month,'-01')) as transaction_date"),
                DB::raw('2 as change_days'),
                DB::raw("'Monthly Accrual' as reason")
            );

        // Merge both
        $transactions = $leaveApplications
            ->unionAll($leaveAccruals)
            ->orderBy('transaction_date', 'asc')
            ->get();

        // Get opening balance
        $balance = DB::table('leave_balances')
            ->where('user_id', $userId)
            ->value('remaining');

        // Reverse calculate running balance
        $runningBalance = $balance;
        $history = [];

        foreach ($transactions->reverse() as $row) {
            $history[] = [
                'transaction_date' => date('d-m-Y', strtotime($row->transaction_date)),
                'change' => $row->change_days,
                'balance' => $runningBalance,
                'reason' => $row->reason
            ];

            $runningBalance -= $row->change_days;
        }

        return response()->json(array_reverse($history));
    }
}
