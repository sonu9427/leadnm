<?php

namespace App\Repositories;

use App\Interfaces\UserRepositoryInterface;
use App\Models\User;
use App\Models\Address;
use App\Models\UserOrganization;
use App\Models\Role;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRepository implements UserRepositoryInterface
{
  public function store($data)
  {
    $address = new Address();
    $address->fulladdress = $data['fulladdress'];
    $address->save();

    $file = $data->file('avatar');
    $fileName = time() . '.' . $file->getClientOriginalExtension();
    $file->move(public_path() . '/uploads/user/', $fileName);

    // Save User
    $user = new User();
    $user->user_name = $data['user_name'];
    $user->email = $data['email'];
    $user->avatar = $fileName ?? null; // Handle nullable fields
    $user->employee_id = $data['employee_id'];
    $user->address_id = $address->id;
    $user->is_active = $data['is_active'];
    $user->save();

    $userOrganization = new UserOrganization();
    $userOrganization->user_id  = $user->id;
    $userOrganization->first_name = $data['first_name'];
    $userOrganization->last_name = $data['last_name'];
    $userOrganization->date_hired = $data['date_hired'];
    $userOrganization->save();

    DB::table('user_roles')->insert([
      'user_id' => $user->id, // Get the user's ID
      'role_id' => $data['role_id'], // The role ID you want to assign
      'created_at' => now(),
      'updated_at' => now(),
    ]);
  }

  public function getEmployeeId()
  {
    $user = User::get('address_id')->first();
    return $employee_Id = ($user->address_id ?? 0) + 1;
  }

  public function getRoleName()
  {
    return $role = Role::pluck('name', 'id');
  }

  // public function getUserList11($roleId = null, $date = null)
  // {
  //   $users = User::with(['roles', 'organization']);

  //   if (!empty($roleId)) {
  //     $users->whereHas('roles', function ($query) use ($roleId) {
  //       $query->where('roles.id', $roleId);
  //     });
  //   }

  //     if (!empty($date)) {
  //       $users->whereHas('organization', function ($query) use ($date) {
  //           $query->where('date_hired', $date);
  //       });
  //   }

  //   $result = $users->get();

  //   return $result;
  // }

  

  public function edit($id)
  {
    $users = User::with(['roles', 'organization', 'address'])->findOrFail($id);
    return $users;  
  }

 public function getUserList222222($roleId = null, $date = null)
{
    // Initialize an empty array to hold results
    $users = [];

    // Query the database
    DB::table('users')
        // ->join('user_organization', 'users.id', '=', 'user_organization.user_id')
        // ->join('user_roles', 'users.id', '=', 'user_roles.user_id')
        // ->join('roles', 'roles.id', '=', 'user_roles.role_id')
        // ->select('users.id as user_id', 'users.user_name', 'user_organization.first_name', 'user_organization.last_name', 'user_organization.date_hired', 'roles.name as role_name')
    ->select('users.id as user_id', 'users.user_name')
        // ->when($roleId, function ($query) use ($roleId) {
        //     return $query->where('roles.id', $roleId);
        // })
        // ->when($date, function ($query) use ($date) {
        //     return $query->whereDate('user_organization.date_hired', '>=', $date);
        // })
        ->orderBy('id')  // Add the orderBy clause
        ->chunk(1000, function ($chunk) use (&$users) {
            // Merge each chunk of users into the main users array
            $users = array_merge($users, $chunk->toArray());
        });

    return collect($users); // Return the users as a collection
}

public function getUserList66($roleId = null, $date = null, $perPage = 10)
{
    $query = DB::table('users')
        ->select('users.id as user_id', 'users.user_name')
        ->orderBy('id');

    if ($roleId) {
        $query->where('users.role_id', $roleId);
    }

    if ($date) {
        $query->whereDate('users.created_at', $date);
    }

    // Get the current page from the request
    $page = request('start', 0) / $perPage + 1;

    return $query->paginate($perPage, ['*'], 'page', $page);
}

public function getUserList($roleId = null, $date = null, $perPage = 10)
  {
    $users = User::with(['roles', 'organization']);

    if (!empty($roleId)) {
      $users->whereHas('roles', function ($query) use ($roleId) {
        $query->where('roles.id', $roleId);
      });
    }

      if (!empty($date)) {
        $users->whereHas('organization', function ($query) use ($date) {
            $query->where('date_hired', $date);
        });
    }

    $page = request('start', 0) / $perPage + 1;

    return $users->paginate($perPage, ['*'], 'page', $page);

    // $result = $users->get();

    // return $result;
  }


  
}
