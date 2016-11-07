<?php

namespace App\Http\Controllers\Auth;

use Auth;
use App\UserToken;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class MagicLoginController extends Controller
{
    
    public function show()
    {
        return view('auth.magic.login');
    }
    
    public function sendToken(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email|max:255|exists:users,email'
        ]);
        
        UserToken::storeToken($request);
        
        UserToken::sendMail($request, [
            'remember' => $request->has('remember'),
            'email' => $request->get('email'),
        ]);
        
        
        return back()->with('success', 'We\'ve sent you a magic link! The link expireres in 5 minutes');
        
    }
    
    
    
    public function authenticate(Request $request, UserToken $token)
    {
        if ($token->isExpired()) {
            return back()->with('error', 'That magic link has expired.');
        }
        
        Auth::login($token->user, $request->remember);
        
        return redirect('home');
    }
    
}
