<?php

namespace App\Models;

enum UserRole: string {
    case ENCODER = 'encoder';
    case ADMIN = 'admin';
    case SUPER_ADMIN = 'super_admin';
}