<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\ApiController;
use App\Mail\UserCreated;
use App\Transformers\UserTransformer;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;

class UserController extends ApiController
{
    public function __construct(){
        $this->middleware('client.credentials')->only(['store' , 'resend']);
        $this->middleware('auth:api')->except(['store' , 'verify', 'resend']);
        $this->middleware('transform.input:' . UserTransformer::class)->only(['store' , 'update']);
    }

    public function index()
    {
        $users = User::all();
        return $this->showAll($users);
    }

    public function store(Request $request)
    {
        $rules = [
          'name'    => 'required',
          'email'    => 'required|email|unique:users',
          'password'    => 'required|min:6|confirmed',
        ];
        $this->validate($request , $rules);
        $data = $request->all();
        $data['password'] = bcrypt( $request->password);
        $data['verified'] = User::UNVERIFIED_USER;
        $data['verification_token'] = User::generateVerificationCode();
        $data['admin'] = User::REGULAR_USER;

        $user = User::create($data);
        return $this->showOne($user , 201);

    }

    public function show(User $user)
    {
        return $this->showOne($user);
    }


    public function update(Request $request, User $user)
    {
        $rules = [
            'email'    => 'email|unique:users,email,'.$user->id,
            'password'    => 'min:6|confirmed',
            'admin' => 'in:'.User::REGULAR_USER . ',' . User::ADMIN_USER ,
        ];
        if ($request->has('name')){
            $user->name = $request->name;
        }
        if ($request->has('email') && $user->email != $request->email){
            $user->email = $request->email;
            $user->verified = User::UNVERIFIED_USER;
            $user->verification_token = User::generateVerificationCode();
        }
        if ($request->has('password')){
            $user->password = bcrypt($request->password);
        }
        if ($request->has('admin')){
            if (! $user->isVerified()){
                return $this->errorResponse(  'only verified your account to be admin' ,409);
            }
            $user->admin = $request->admin ;
        }
        if ($user->isClean()){
            return $this->errorResponse( 'you need to satisfy different value' ,422);
        }
        $user->save();
        return $this->showOne($user);
    }

    public function destroy(User $user)
    {
        $user->delete();
        return $this->showOne($user);

    }

    public function verify($token){
        $user  = User::where('verification_token' , $token)->firstOrFail();
        $user->verified = User::VERIFIED_USER;
        $user->verification_token = null;
        $user->save();
        return $this->showMessage('the account has been verified successfully');
    }

    public function resend(User $user){
        if ($user->isVerified()){
            return $this->errorResponse('this user is already verified' , 409);
        }

        retry(5,function ()use ($user){
            Mail::to($user)->send(new UserCreated($user));
        },100);

        return $this->showMessage('the verification email has been resend');
    }

}
