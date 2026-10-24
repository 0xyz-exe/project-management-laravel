<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Milestone extends Model
{
    protected $fillable = ['project_id', 'name', 'due_date', 'completed'];
    protected $casts = ['due_date' => 'date', 'completed' => 'boolean'];
}
