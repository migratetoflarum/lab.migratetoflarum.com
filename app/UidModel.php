<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

/**
 * @property string $uid
 */
abstract class UidModel extends Model
{
    public function save(array $options = [])
    {
        if (!$this->uid) {
            $this->uid = Uuid::uuid4()->toString();
        }

        return parent::save($options);
    }
}
