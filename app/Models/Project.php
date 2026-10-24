<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = ['owner_id', 'name', 'description', 'deadline'];
    protected $casts = ['deadline' => 'date'];
    public function members() { return $this->belongsToMany(User::class)->withPivot('role'); }
    public function tasks() { return $this->hasMany(Task::class); }
    public function milestones() { return $this->hasMany(Milestone::class); }
}
