<?php

namespace App\Models;

use App\Core\Models\Model;

class RequestEdit extends Model
{
    protected string $table = 'request_edit_tbl';
    protected string $primary_key = 'id';

    protected array $fillable = [
        'encoder', 
        'app_id',
        'old',
        'new',
        'is_read',
        'status',
        'datetime_request',
        'datetime_action',
    ];
}