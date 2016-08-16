<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * FacebookAdset Entity.
 */
class FacebookAdset extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        'user_id' => true,
        'adset_id' => true,
        'bid' => true,
        'budget' => true,
        'auto_bid' => true,
        'campaign_id' => true,
        'account_id' => true,
        'adset' => true,
        'campaign' => true,
        'account' => true,
        'status'=>true,
        'billing_event'=>true,
        'optimization_goal'=>true,
        'updated'=>true,
        'name'=>true,
    ];
}
