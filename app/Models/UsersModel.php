<?php

namespace App\Models;

class UsersModel extends ContainerModel
{
    protected $table = 'users';

    protected $allowedFields = [
        'first_name',
        'last_name',
        'username',
        'email',
        'password',
        'phone',
        'gender',
        'bio',
        'dob',
        'avatar_url',
        'is_verified'
    ];
}
