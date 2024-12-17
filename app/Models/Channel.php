<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    protected $fillable = ['name'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'channel_user');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
