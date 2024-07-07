<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    protected $hidden = ['created_at', 'updated_at'];
    use HasFactory, Notifiable;

    protected $fillable = [
        'userId',
        'firstName',
        'lastName',
        'email',
        'password',
        'phone',
    ];

    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'userId'; // Set the primary key

    protected static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->userId = (string) Str::uuid();
        });
    }

    public function organisations()
    {
        return $this->belongsToMany(Organisation::class, 'organisation_user', 'userId', 'orgId');
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'email' => $this->email,
            'userId' => $this->userId,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
        ];
    }
}
