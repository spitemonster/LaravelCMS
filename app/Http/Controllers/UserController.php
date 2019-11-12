<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Hash;
use Uuid;

class UserController extends Controller
{
    public function newApiToken(Request $request) {
        $user = User::where('user_id', $request->query('user_id'))->first();

        $user->api_token = Uuid::generate(4)->string;

        $user->save();

        $successMsg = array(
            'status' => 'success',
            'message' => 'New API Token Generated'
        );

        return $successMsg;
    }

    public function updateUser(Request $request) {
        $user = User::where('user_id', $request->query('user_id'))->first();

        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->password = Hash::make($request->input('newPassword'));

        $user->save();

        $successMsg = array(
            'status' => 'success',
            'message' => 'User info updated. You will be logged out momentarily.'
        );

        return $successMsg;
    }
}
