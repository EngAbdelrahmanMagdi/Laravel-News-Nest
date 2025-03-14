<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function articles()
    {
        return $this->hasMany(Article::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}

