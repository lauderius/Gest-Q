<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Queue extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'group_name', 'active', 'avg_service_sec'];

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
