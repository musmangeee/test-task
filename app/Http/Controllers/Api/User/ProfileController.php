<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Mail\PinCodeVerification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class ProfileController extends Controller
{
    public function update_profile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string',
            'username' => 'nullable|string|unique:users|min:4|max:20',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,bmp,svg,png|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Kindly check the following fields.',
                'error' => $validator->errors()], 401);
        }

        if (!Auth::guard('api')->user()) {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'Session Expired or User not found!',
                ]
                , 404);
        }
        $input = $request->all();
        if ($file = $request->file('avatar')) {
            $name = time() . '.' . $file->getClientOriginalExtension();
            $file = Image::make($file)->resize(256, 256, function ($constraint) {
                $constraint->upsize();
            });

            Storage::put('profile/' . $name, $file->stream());
            $input['avatar'] = Storage::path('profile\\' . $name);
        }

        $user = Auth::guard('api')->user()->update($input);


        return response()->json(
            [
                'status' => true,
                'message' => 'User has been updated successfully',
                'data' => Auth::guard('api')->user(),
            ]
            , 200);
    }
}
