<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    protected $fillable = [
        'description',
        'date',
        'hourly_rate',
        'additional_charges',
        'total_remuneration',
    ];

    public function taskAssignments(): HasMany
    {
        return $this->hasMany(TaskAssignment::class);
    }

    public function calculateRemuneration(): float
    {
        $totalHours = $this->taskAssignments->sum('hours_spent');
        $base = $totalHours * $this->hourly_rate;
        return $base + $this->additional_charges;
    }
}
