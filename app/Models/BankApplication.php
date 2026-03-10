<?php

namespace App\Models;

use App\Core\Models\Model;

class BankApplication extends Model
{
    protected string $table = 'bank_application_tbl';
    protected string $primary_key = 'id';

    protected array $fillable = [
        'client_id',
        'bank_submitted_id', 
        'date_submitted',
        'agent',
    ];
}
