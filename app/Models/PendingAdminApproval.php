<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PendingAdminApproval extends Model
{
    use HasUuids;

    protected $fillable = [
        'email', 'full_name', 'role', 'password_hash', 'status',
    ];

    protected $hidden = ['password_hash'];
}
