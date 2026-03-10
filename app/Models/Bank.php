<?php

namespace App\Models;

use App\Core\Models\Model;

class Bank extends Model
{
    protected string $table = 'bank_list_tbl';
    protected string $primary_key = 'id';

    protected array $fillable = [
        'name', 
        'short_name',
        'expiry_months',
        'is_active',
        'created_at',
        'updated_at',
    ];
}