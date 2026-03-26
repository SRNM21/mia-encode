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
        'team',
        'last_password_update',
        'status'
    ];

    protected array $hidden = [
        'pass'
    ];

    protected array $casts = [
        'role' => UserRole::class
    ];

    public function getPreference(): Setting
    {
        return Setting::query()
            ->where('user_id', '=', $this->id)
            ->first();
    }

    public function checkPassword(string $password)
    {
        return password_verify($password, $this->getAttribute('pass'));
    }

    public function getRoleEnum(): ?UserRole
    {
        $role = $this->getAttribute('role');

        if ($role instanceof UserRole) 
        {
            return $role;
        }

        if (is_string($role)) 
        {
            return UserRole::tryFrom($role);
        }

        return null;
    }

    public function isSupAdmin(): bool 
    {
        return $this->role === UserRole::SUPER_ADMIN;
    }

    public function isAdmin(): bool 
    {
        return $this->role === UserRole::ADMIN;
    }

    public function isEncoder(): bool 
    {
        return $this->role === UserRole::ENCODER;
    }
}