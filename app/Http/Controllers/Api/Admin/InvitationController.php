<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Mail\InvitationMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Validator;

class InvitationController extends Controller
{

    public function __construct() {
        Validator::extend("emails", function($attribute, $value, $parameters) {
            $rules = [
                'email' => 'required|email',
            ];
            foreach ($value as $email) {
                $data = [
                    'email' => $email
                ];
                $validator = Validator::make($data, $rules);
                if ($validator->fails()) {
                    return false;
                }
            }
            return true;
        });
    }
    public function send_invitations(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'emails' => 'required|emails'
        ]);



        if($validator->fails())
        {
            return [
                'status' => false,
                'message' => 'Kindly check the following fields.',
                'error' => $validator->errors()
            ];
        }

        if (Auth::guard('api')->user()->hasRole('admin')) {
            $emails = array_unique($request->emails);


            foreach ($emails as $email){
                Mail::to($email)->send(new InvitationMail());
            }

            return [
                'status' => true,
                'message' => 'Emails sent successfully.'
            ];

        } else {
            abort(403, 'Unauthorized Action');
        }

    }
}
