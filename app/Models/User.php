<?php

namespace App\Models;

use App\Core\Models\Model;

class User extends Model
{
    protected string $table = 'account_tbl';
    protected string $primary_key = 'id';

    protected array $fillable = [
        'username', 
        'email',
        'pass',
        'role',
        'status'
    ];

    protected array $hidden = [
        'pass'
    ];

    public function checkPassword(string $password)
    {
        return password_verify($password, $this->getAttribute('pass'));
    }
}