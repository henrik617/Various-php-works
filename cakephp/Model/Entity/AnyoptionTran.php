<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * AnyoptionTran Entity.
 */
class AnyoptionTran extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        'dates' => true,
        'dp' => true,
        'registrations' => true,
        'ftd' => true,
        'house_win' => true,
        'campaign_id' => true,
        'adset_id' => true,
        'ad_id' => true,
        'adset_name' => true,
        'adgroup_id' => true,
        'source' => true,
        'manager' => true,
        'campaign' => true,
        'adset' => true,
        'ad' => true,
        'adgroup' => true,
        'combination' => true

    ];
}
