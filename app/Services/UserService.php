<?php

namespace App\Services;

use App\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
class UserService
{

    protected $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }
    public function getIndex(){

        return $this->userRepository->index();
    }

    public function createUser(array $data)
    {

        $data['password'] = Hash::make($data['password']);


        return $this->userRepository->store($data);
    }
    public function updateUser($id,  $data){

// dd( $data);
        if(!empty($data['password'])){
            $data['password'] = Hash::make($data['password']);
        }else{

            $data = Arr::except($data,['password','confirm-password']);
        }


        return $this->userRepository->update( $id, $data);

    }


    public function getUserByEmail(string $email)
    {

        return $this->userRepository->findByEmail($email);
    }
}
