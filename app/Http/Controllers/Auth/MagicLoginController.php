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
    
    public function sendToken(Request $request, UserToken $token)
    {
        $this->validate($request, [
            'email' => 'required|email|max:255|exists:users,email'
        ]);
        
        UserToken::storeToken($request);
        
        UserToken::sendMail($request);
        
        
        return back()->with('success', 'We\'ve sent you a magic link! The link expires in 5 minutes');
        
    }
    
    
    
    public function authenticate(Request $request, UserToken $token)
    {
        if ($token->isExpired()) {
            $token->delete();
            return back()->with('error', 'That magic link has expired.');
        }
        
        if (!$token->belongsToEmail($request->email)) {
            $token->delete();
            return back()->with('/login/magiclink')->with('error', 'Invalid magic link. Resubmit email address again to receive a new magic link');
        }
        
        Auth::login($token->user, $request->remember);
        
        $token->delete();
        
        return redirect('home');
        
    }
    
}
