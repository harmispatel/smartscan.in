<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Show Login Form
     */
    public function showLogin()
    {
        return view('auth.login');
    }


    /**
     * Authenticate the User
     *
     * @param Request $request
     */
    public function login(Request $request)
    {

        $request->validate([
            'email' => 'required',
            'password' => 'required'
        ]);

        $input = $request->except('_token');

        if (Auth::attempt($input))
        {
            if (Auth::user()->user_type == 1)
            {
                $username = Auth::user()->firstname." ".Auth::user()->lastname;
                return redirect()->route('admin.dashboard')->with('success', 'Welcome '.$username);
            }
            else
            {
                $username = Auth::user()->firstname." ".Auth::user()->lastname;
                return redirect()->route('client.dashboard')->with('success', 'Welcome '.$username);
            }
            // return back()->with('error', 'Kindly Login with Active Admin User.');
        }

        return back()->with('error', 'Please Enter Valid Email & Password');
    }


    /**
     * Logout the User
     */
    public function logout()
    {
        Auth::logout();
        return redirect()->route('login');
    }
}
