<?php

namespace App\Models;

use App\Notifications\TwoFactorCode;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Psy\Util\Str;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function last_access_session() {
        return $this->hasOne(AccessSession::class)->latest();
    }

    public function generate2FACode() {
        $data2Fa = $this->createSecretNumber();
        $access_session = AccessSession::where('user_id', $this->id);
        if(!$access_session->first()) {
            AccessSession::create([
                'user_id' => $this->id,
                'token' => $data2Fa[1]
            ]);
            $this->notify(new TwoFactorCode($data2Fa[0]));
            return $data2Fa[1];
        }
        $this->notify(new TwoFactorCode($data2Fa[0]));
        $access_session->update(['token' => $data2Fa[1], 'created_at' => now()]);
        return $data2Fa[1];
    }

    public function createSecretNumber() {
        $secretNumber = rand(100000, 999999);
        $token = hash('sha256', $secretNumber);
        return [$secretNumber, $token];
    }

    public function reset2FA() {
        AccessSession::where('user_id', $this->id)->update(['token' => $this->createSecretNumber()[1]]);
    }

    public function setActivated2Fa() {
        AccessSession::where('user_id', $this->id)->update(['token' => 1, 'created_at' => now()]);
    }
}
