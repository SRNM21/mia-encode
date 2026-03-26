<?php

namespace App\Models;

use App\Core\Models\Model;

class Setting extends Model
{
    protected string $table = 'settings_tbl';
    protected string $primary_key = 'user_id';

    protected array $fillable = [
        'user_id', 
        'preference',
    ];
}