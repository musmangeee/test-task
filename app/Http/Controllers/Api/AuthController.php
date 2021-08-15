<?php

namespace App\Http\Controllers\Api;

use App\Mail\PinCodeVerification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Validator;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Intervention\Image\Facades\Image;

class AuthController extends Controller
{
    public $successStatus = 200;

    /**
     * Create user
     *
     * @param  [string] name
     * @param  [string] email
     * @param  [string] password
     * @param  [string] password_confirmation
     * @return [string] message
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string',
            'email' => 'required|string|email|unique:users',
            'username' => 'required|string|unique:users|min:4|max:20',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,bmp,svg,png|max:5000',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Kindly check the following fields.',
                'error' => $validator->errors()], 401);
        }
        $input = $request->all();
        unset($input['role']);
        $input['password'] = bcrypt($input['password']);
        $input['pin'] = rand(100000, 999999);
        if ($file = $request->file('avatar')) {
            $name = time() . '.' . $file->getClientOriginalExtension();
            $file = Image::make($file)->resize(256, 256, function ($constraint) {
                $constraint->upsize();
            });

            Storage::put('profile/' . $name, $file->stream());
            $input['avatar'] = Storage::path('profile\\' . $name);
        }

        $input['registered_at'] = Carbon::now();
        $user = User::create($input);
        $user->assignRole('user');
        Mail::to($user->email)->send(new PinCodeVerification($user->pin));
        $user['token'] = $user->createToken('MyApp')->accessToken;


        return response()->json(
            [
                'status' => true,
                'message' => 'User has been registered successfully',
                'data' => $user,
            ]
            , 200);
    }

    /**
     * Login user and create token
     *
     * @param  [string] email
     * @param  [string] password
     * @param  [boolean] remember_me
     * @return [string] access_token
     * @return [string] token_type
     * @return [string] expires_at
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
            'remember_me' => 'boolean'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Kindly check the following field.',
                'error' => $validator->errors()
            ], 401);
        }

        $credentials = request(['email', 'password']);
        if (!Auth::attempt($credentials))
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized User!'
            ], 401);
        $user = $request->user();

        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;
        if ($request->remember_me)
            $token->expires_at = Carbon::now()->addWeeks(1);
        $token->save();

        return response()->json([
            'status' => true,
            'message' => 'Logged in successfully!',
            'data' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->getRoleNames(),
                'access_token' => $tokenResult->accessToken,
                'token_type' => 'Bearer',
                'expires_at' => Carbon::parse(
                    $tokenResult->token->expires_at
                )->toDateTimeString()
            ]
        ]);
    }

    /**
     * Logout user (Revoke the token)
     *
     * @return [string] message
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    /**
     * Get the authenticated User
     *
     * @return [json] user object
     */
    public function user(Request $request)
    {
        if (Auth::user()->getRoleNames()[0] == 'user') {
            return response()->json(
                [
                    'data' => $request->user(),
                ]);
        } elseif (Auth::user()->getRoleNames()[0] == 'business') {
            $business_user = User::where('id', '=', $request->user()->id)->with('business_profile')->first();
            return response()->json(
                [
                    'data' => $business_user,
                ]);
        } else {
            $res = [
                'message' => 'User role not exist',
                'status' => false,
            ];
            return response()->json($res);
        }

    }

    public function change_password(Request $request)
    {
        $validator = Validator::make($request->all(), [

            'password' => ['required', 'string', 'min:8', 'same:confirm-password'],
            'old-password' => ['required', 'string', 'min:8'],

        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }


        if (Auth::guard('api')->user()) {
            $user = Auth::guard('api')->user();

            if ($user) {
                if (\Hash::check($request->old_password, $user->password)) {
                    $user->password = bcrypt($request->password);
                    $user->save();
                    return response()->json(['message' => 'Password changed successfully', 'status' => 'true'], 201);
                } else {
                    return response()->json(['message' => 'Incorrect old password', 'status' => 'false'], 400);
                }

            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'User not found.'
            ], 404);
        }


    }

    public function forgot_password(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        } else {
            Password::sendResetLink(
                $request->only('email')
            );
            return response()->json([
                "status" => true,
                "message" => 'Reset password link sent on your email id.'
            ], 200);
        }
    }


}
