<?php


namespace App\Model\Table;


use Cake\ORM\Table;
use Cake\I18n\Time;
use Cake\Datasource\ConnectionManager;

class FacebookCampaignsTable extends Table
{
    use TableTrait;

    public function initialize(array $config)
    {
        $this->belongsTo('FacebookAdAccounts', [
            'foreignKey' => 'account_id',
            'bindingKey' => 'account_id',
        ]);
    }

    public function updateOrInsertMany($rows, $user_id)
    {
        return $this->updateOrInsertManyByField($rows, $user_id, 'campaign_id');
    }

    public function getCampaignOptions($user_id, $account_id, array $filters = array())
    {
        return $this->find()
            // ->contain(['FacebookAdAccounts'])
            // ->where(['FacebookAdAccounts.is_enabled' => true])
            ->where([
                'user_id' => $user_id,
                'account_id' => $account_id
            ])
            ->where($filters)
            ->order(['FacebookCampaigns.name' => 'ASC'])
            ->combine('campaign_id', 'name')
            ->toArray();
    }


    public function getCampaignIds($user_id, $account_id)
    {
        return $this->find('all')
            ->select(['campaign_id'])
            ->where([
                'user_id' => $user_id,
                'account_id' => $account_id
            ])->extract('campaign_id')->toArray();
    }


    public function getAll($user_id, $account_id, $campaign_id = 'all', array $filters = array())
    {
        $query = $this->find()->select([
            'account_id', 'campaign_id', 'name', 'objective', 'buying_type',
            'start_time', 'stop_time', 'status'
        ]);

        $query->where([
            'user_id' => $user_id,
            'account_id' => $account_id
        ]);

        if ($campaign_id != 'all') {
            $query->where(['campaign_id' => $campaign_id]);
        }
        $query->where($filters);

        $data = $query->map(function ($row) {
            return $row->toArray();
        })->toArray();

        return $data;
    }
    public function getCampaignIdWithNames($user_id, $account_id)
    {
        $conn = ConnectionManager::get('default');
        $stmt = $conn->execute(
            "select campaign_id, name from facebook_campaigns WHERE user_id = '$user_id' and account_id='$account_id'"
        );
        return $stmt ->fetchAll('assoc');
        
        // return $this->find("all", 
        //     array("fields" => array('camp_id' => 'cast(campaign_id as char(30))', 'name'),
        //         "conditions" => array('user_id' => $user_id, 'account_id' => $account_id)))->toArray();
    }
}
