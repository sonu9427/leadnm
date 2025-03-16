<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;
use DataTables;

class UserController extends Controller
{
    private $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function index()
    {
        $data['employee_id'] = $this->userRepository->getEmployeeId();
        $data['roles'] = $this->userRepository->getRoleName();

        return  view('index', $data);
    }

    public function getUserList111(Request $request)
    {
        $date = $request->query('dt');
        $roleId = $request->query('role_id');
        $query = $this->userRepository->getUserList($roleId, $date);
        return datatables()->of($query)
            ->editColumn('chkdelete', function ($row) {
                $btndel = '<input type="checkbox" name="deleteUser" id="deleteUser" value="' . $row->id . '">';
                return $btndel;
            })
            ->addColumn('action', function ($row) {
                $btnEdit = '<a href="#" data-bs-toggle="modal" data-bs-target="#addEmployeeModal" class="btn btn-info btn-xs edit-user" id="edituser" title="Edit" data-url="' . route('edit', $row->id) . '">Edit</a>';
                $btnDelete = '<a href="#" class="btn btn-danger btn-xs delete-news" title="Edit" data-url="' . route('user.destroy', $row->id) . '">Delete</a>';
                return $btnEdit . ' ' . $btnDelete;
            })
            ->rawColumns(['chkdelete', 'action'])
            ->make(true);
    }

     public function getUserList11111111(Request $request)
    {
        $date = $request->query('dt');
        $roleId = $request->query('role_id');

        // Fetch the data using the repository
        $users = $this->userRepository->getUserList($roleId, $date);

      //  dd($users);

        return datatables()->of($users)
             ->addIndexColumn()
            // ->addColumn('full_name', function ($user) {
            //     return $user->first_name . ' ' . $user->last_name;
            // })
            ->addColumn('action', function ($user) {
                // Add action buttons or links as needed
                return '<button class="btn btn-primary">Action</button>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

//     public function getUserList(Request $request)
// {
//     $date = $request->query('dt');
//     $roleId = $request->query('role_id');
//     $perPage = $request->query('length', 10); // DataTables page length
//     $page = ($request->query('start', 0) / $perPage) + 1;

//     // Fetch paginated data
//     $users = $this->userRepository->getUserList($roleId, $date, $perPage);

//     return response()->json([
//         "draw" => intval($request->query('draw')), 
//         "recordsTotal" => $users->total(),
//         "recordsFiltered" => $users->total(),
//         "data" => $users->items() // Extracts only paginated data
//     ]);
// }


public function getUserList(Request $request)
{
    $date = $request->query('dt');
    $roleId = $request->query('role_id');
    $perPage = $request->query('length', 10); // DataTables page length
    $page = ($request->query('start', 0) / $perPage) + 1;

    // Fetch paginated data
    $users = $this->userRepository->getUserList($roleId, $date, $perPage);

    // Modify each record to add the "action" column
    $users->getCollection()->transform(function ($user) {
        $user->action = '<button class="btn btn-primary">Edit</button>';
        return $user;
    });

    return response()->json([
        "draw" => intval($request->query('draw')), 
        "recordsTotal" => $users->total(),
        "recordsFiltered" => $users->total(),
        "data" => $users->items() // Extracts only paginated data
    ]);
}



    public function store(Request $request)
    {

        $validated = $request->validate([
            'fulladdress' => 'required|string',
            'user_name'   => 'required|string',
            'email'       => 'required|email',
            'employee_id' => 'required|integer',
        ]);

        DB::beginTransaction();

        try {
            $this->userRepository->store($request);
            DB::commit();

            return response()->json(['message' => 'Record saved successfully!'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to save record', 'details' => $e->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        return $this->userRepository->edit($id);
    }
}
