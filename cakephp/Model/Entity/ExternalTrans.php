<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * AdInsight Entity.
 */
class ExternalTrans extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        'adgroup_name' => true,
        'adgroup_id' => true,
        'impressions' => true,
        'clicks' => true,
        'reach' => true,
        'frequency' => true,
        'spend' => true,
        'fb_mobile_purchase' => true,
        'request_date' => true,
        'date_start' => true,
        'date_stop' => true,
        'campaign_id' => true,
        'account_id' => true,
        'adset_id' => true,
        'country' => true,
        'e_registrations' => true,
        'e_ftd' => true,
        'e_house_win' => true,
        'e_dp' => true,
        'e_source' => true,
        'e_manager' => true,
        'adgroup' => true,
        'campaign' => true,
        'account' => true,
        'adset' => true,
        'registrations'=>true,
        'installs'=>true,
        'campaign_name' => true,
        'account_name' => true,
        'adset_name' => true,
        'offsite_conversions'=>true,
        'col_type'=>true,
        'user_id'=>true,

        //  quick hack, this should be fixed to allow only custom fields
        '*' => true,
    ];


    public function GetSniperMaxCPIBid ()
    {
        return 5;
    }

}
