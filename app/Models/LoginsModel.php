<?php

namespace App\Models;

class LoginsModel extends ContainerModel
{
    protected $table = 'logins';

    protected $allowedFields = [
        'user_id',
        'token'
    ];
}
