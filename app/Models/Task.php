<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    // Task status constants used throughout the application to enforce allowed transitions
    // These string values are stored in the `status` column of the tasks table.
    public const STATUS_DRAFT = 'Draft';
    public const STATUS_IN_PROCESS = 'In-Process';
    public const STATUS_COMPLETED = 'Completed';

    // $fillable: fields that are allowed for mass-assignment (Task::create($data))
    // Keep this list minimal and explicit to avoid unintended updates.
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'priority',
        'status',
        'due_date',
    ];

    // Cast `due_date` to a Carbon date automatically when accessing the model.
    protected $casts = [
        'due_date' => 'date',
    ];
}
