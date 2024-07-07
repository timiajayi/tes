<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Organisation extends Model
{
    protected $hidden = ['created_at', 'updated_at'];
    use HasFactory;

    protected $fillable = [
        'orgId', 
        'name', 
        'description'
    ];

    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'orgId'; // Set the primary key

    protected static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->orgId = (string) Str::uuid();
        });
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'organisation_user', 'orgId', 'userId');
    }
}
