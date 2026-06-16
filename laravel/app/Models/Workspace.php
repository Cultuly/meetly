<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Workspace extends Model
{
    protected $fillable = ['owner_id', 'name', 'visibility', 'invite_token'];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function channels()
    {
        return $this->hasMany(Channel::class);
    }

    public function members()
    {
        return $this->belongsToMany(User::class);
    }
}