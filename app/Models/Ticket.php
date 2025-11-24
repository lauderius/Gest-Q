<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'queue_id', 'ext_id', 'number', 'status',
        'person_name', 'notes', 'started_at', 'finished_at'
    ];

    public function queue()
    {
        return $this->belongsTo(Queue::class);
    }
}
