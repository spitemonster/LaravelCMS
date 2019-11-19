<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Hash;
use Uuid;
use Auth;

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

    public function getUser(Request $request) {
        if (!$request->query('user_id')) {
            $authUser = Auth::user();

            if (!$authUser) {
                return 'You must be logged in or have a valid User ID';
            }

            return $authUser;
        }

        $user = User::where('user_id', $request->query('user_id'))->first();

        return $user;
    }

    public function deleteUser(Request $request) {
        $requestingUser = User::where('api_token', $request->query('api_token'))->first();
        $targetUser = User::where('user_id', $request->query('user_id'))->first();

        // check if user is NOT a superuser
        if (!$requestingUser->superuser) {

            // if they're NOT a superuser, they still are allowed to delete their own account
            if ($requestingUser == $targetUser) {
                $targetUser->delete();

                $allowedData = array(
                    'status' => 'success',
                    'message' => 'User successfully deleted',
                    'users' => User::all()
                );

                return $allowedData;
            }

            // if they attempt to delete an account that is not their own, they can go straight to heck
            $disallowedData = array(
                'status' => 'error',
                'message' => 'You do not have the required permission to complete that action',
            );

            return $disallowedData;
        }

        // if they ARE a superuser they can do whatever they darn well please
        $targetUser->delete();

        $allowedData = array(
            'status' => 'success',
            'message' => 'User successfully deleted',
            'users' => User::all()
        );

        return $allowedData;
    }
}
