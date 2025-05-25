<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    protected $fillable = ['name'];

    public function taskAssignments(): HasMany
    {
        return $this->hasMany(TaskAssignment::class);
    }
}
