<?php

namespace App;

use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

class UserToken extends Model
{
    protected $fillable = ['user_id', 'token'];
    
    
   
    public static function storeToken(Request $request)
    {
        
        $user = self::getUserByEmail($request->get('email'));
        
        
        $user->token->delete();
        
        return self::create([
            'user_id' => $user->id,
            'token'   => str_random(50)
        ]);
    }
    
    protected static function getUserByEmail($value)
    {
        return User::where('email', $value)->firstOrFail();
    }
    
    
    
    protected static function sendMail(Request $request, array $options)
    {
        $user = self::getUserByEmail($request->get('email'));
        
        $url = url('/login/magiclink/' . $user->token->token . '?' . http_build_query($options));
        
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
	    return $this->created_at->diffInSeconds(Carbon::now()) > Carbon::parse('-15 minutes');
	}
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    
}

