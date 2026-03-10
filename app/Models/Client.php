<?php

namespace App\Models;

use App\Core\Models\Model;

class Client extends Model
{
    protected string $table = 'client_tbl';
    protected string $primary_key = 'id';

    protected array $fillable = [
        'first_name', 
        'middle_name',
        'last_name',
        'birthdate',
        'mobile_num',
        'first_application',
        'latest_application'
    ];

    public function checkPassword(string $password)
    {
        return password_verify($password, $this->getAttribute('pass'));
    }
}