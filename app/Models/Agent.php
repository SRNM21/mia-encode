<?php

namespace App\Models;

use App\Core\Models\Model;

class Agent extends Model
{
    protected string $table = 'account_tbl';
    protected string $primary_key = 'id';

    protected array $fillable = [
        'employee_id', 
        'username',
        'email',
        'role',
    ];

    protected array $hidden = [
        'pass',
    ];
}