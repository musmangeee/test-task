<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        if(!Auth::user()->pin_verified){
            return view('verify.pin');
        }
        return view('home');
    }

    public function verify_pin(Request $request)
    {
        $request->validate([
            'pin' => 'required|min:6|max:6'
        ]);

        if(Auth::user()->pin == $request->pin){
            Auth::user()->pin_verified = 1;
            Auth::user()->save();
        }else{
            return Redirect::back()->withErrors([ 'The pin you entered is wrong.!']);
        }
        return redirect('home');
    }
}
