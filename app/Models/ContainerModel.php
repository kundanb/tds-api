<?php

namespace App\Models;

use CodeIgniter\Model;

class ContainerModel extends Model
{
    protected $useTimestamps = true;
    protected $useSoftDeletes = true;

    public function removeFields(array &$data, array $fields = [])
    {
        foreach ($fields as $field) {
            unset($data[$field]);
        }
    }
}
