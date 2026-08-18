<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'name',
        'description',
        'owner_name',
        'owner_email',
        'status',
        'priority',
        'start_date',
        'due_date',
        'progress',
        'source',
        'source_reference',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
        'progress' => 'integer',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}