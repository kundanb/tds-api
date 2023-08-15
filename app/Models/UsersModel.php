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

    public function removePrivateFields(array &$user, array $publicFields = [], array $fieldsToRemove = [])
    {
        if (!in_array('password', $publicFields))
            unset($user['password']);

        if (!in_array('deleted_at', $publicFields))
            unset($user['deleted_at']);

        $this->removeFields($user, $fieldsToRemove);
    }
}
