<?php

namespace App\Repositories;

use App\Models\Client;
use App\Models\Staff;
use App\Models\User;
use App\Interfaces\UserRepositoryInterface;
use App\Models\GoverningBody;
use App\Models\GoverningBodyUser;
use Auth;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Support\Facades\DB;

class UserRepository implements UserRepositoryInterface
{
    public function index(){

        $authUser = Auth::user();
        $query = User::query()
                ->where('users.id', '!=', $authUser->id);

        if ($authUser->hasRole('super_admin') ) {

            $query->whereHas('roles', function ($query) {
                $query->where('position_name', '=', 'super_admin');
            });
        }
        elseif($authUser->hasRole('adminMIP')){

            $query->whereHas('roles', function ($query) {
                $query->where('interface', '=', 'admin_mip');
            });
        }
        elseif($authUser->hasRole('adminPN')){

            $query->whereHas('roles', function ($query) {
                $query->where('interface', '=', 'admin_pn');
            });
        }


        return  $query->orderBy('id','desc')->get();

    }
    public function store(array $data): User
    {
        try {
            DB::beginTransaction();

            $user = User::create($data);

            DB::commit(); // Commit the transaction if everything goes well

            return $user;

        } catch (\Exception $e) {
            DB::rollBack(); // Revert all changes if an error occurs
            dd($e->getMessage()); // Debug the exception
        }



    }
    public function update($id, $data){

        try {
            DB::beginTransaction();
                $user=User::where('id',$id)->first();

                $user->update($data);
                $user->password_changes_at = $user->updated_at;
                $user->save();
                $user->roles()->detach();
                $user->assignRole($data['roles']);

                $user->governing_body_user()->delete();

                $governingBodyIds = GoverningBody::all()->pluck('id', 'named')->toArray();

                $governingBodyUserData = [];

            foreach ($data['roles'] as $role) {

                $governingBody = str_contains($role, 'MIP')
                                    ? $governingBodyIds['MIP']
                                    : $governingBodyIds['PN'];

                 $row = [
                    'user_id' => $user->id,
                    'governing_body_id' => $governingBody
                ];

                // Add 'operator' field if role matches
                if ($role == "operatorMIP" || $role == "operatorPN") {
                    $row['operator'] = 1;
                }else{
                    $row['operator'] = 0;
                }


                $governingBodyUserData[] = $row;
            }

                GoverningBodyUser::insert($governingBodyUserData);
                DB::commit();
                return $user;
            } catch (\Exception $e) {
                DB::rollBack(); // Revert all changes if an error occurs
                dd($e->getMessage()); // Debug the exception
            }
    }

    public function findByEmail(string $email): ?User
    {
        // Поиск пользователя по email
        return User::where('email', $email)->first();
    }
}

