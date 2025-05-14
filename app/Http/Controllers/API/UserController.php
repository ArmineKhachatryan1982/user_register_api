<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Api\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends BaseController
{


    public function __construct(protected UserService $userService)
    {
        $this->userService = $userService;

    }

        public function store(UserRequest $request)
    {

        $user = $this->userService->createUser($request->all());

       return $this->sendResponse( $user,'User created successfully');
    }

}
