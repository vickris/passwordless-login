<?php

namespace App;

use App\User;
use Carbon\Carbon;
// use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

class UserToken extends Model
{
    protected $fillable = ['user_id', 'token'];
    
    
    
    public static function sendMail($request)
    {
        $user = User::getUserByEmail($request->get('email'));
        
        if(!$user) {
            return redirect('/login/magiclink')->with('error', 'User not foud. PLease sign up');
        }
        
        $url = url('/login/magiclink/' . $user->token->token . '?' . http_build_query([
            'remember' => $request->get('remember'),
            'email' => $request->get('email'),
        ]));
        
        Mail::raw(
            "<a href='{$url}'>{$url}</a>",
            function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('Click the magic link to login');
            }
        );
    }
    
    
    public function getRouteKeyName()
    {
        return 'token';
    }
    
    public function isExpired()
	{
	    return $this->created_at->diffInMinutes(Carbon::now()) > 5;
	}
	
	public function belongsToUser($email)
    {  
        
        $user = User::getUserByEmail($email);
        
        
        if(!$user) {
            return false;
        } elseif ($user->token == null) {
            //if record was found but no token is associated with it
            return false;
        } else {
            //if the record found has a token and the token value matches what was sent in the email
            return ($this->token === $user->token->token);
        }
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    
}

