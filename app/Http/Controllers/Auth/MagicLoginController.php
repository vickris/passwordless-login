<?php

namespace App\Http\Controllers\Auth;

use Auth;
use App\User;
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
        
        $user = User::getUserByEmail($request->get('email'));
        
        
        if (!$user) {
            return redirect('/login/magiclink')->with('error', 'Please sign up');
        }
        
        UserToken::create([
            'user_id' => $user->id,
            'token'   => str_random(50)
        ]);
        
        UserToken::sendMail($request);
        
        
        return back()->with('success', 'We\'ve sent you a magic link! The link expires in 5 minutes');
        
    }
    
    
    
    public function authenticate(Request $request, UserToken $token)
    {
        if ($token->isExpired()) {
            $token->delete();
            return redirect('/login/magiclink')->with('error', 'That magic link has expired.');
        }
        
        if (!$token->belongsToUser($request->email)) {
            $token->delete();
            return redirect('/login/magiclink')->with('error', 'Invalid magic link. Resubmit email address again to receive a new magic link');
        }
        
        Auth::login($token->user, $request->remember);
        
        $token->delete();
        
        return redirect('home');
        
    }
    
}
