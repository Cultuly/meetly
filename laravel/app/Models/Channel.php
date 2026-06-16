<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    protected $fillable = ['workspace_id', 'name'];

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}