<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * FacebookToken Entity.
 */
class FacebookToken extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        'user_id' => true,
        'access_token' => true,
        'email' => true,
        'user' => true,
    ];
}
