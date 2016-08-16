<?php


namespace App\Model\Table;


use Cake\ORM\Table;
use Cake\I18n\Time;


class FacebookAdsTable extends Table
{
    use TableTrait;

    public function updateOrInsertMany($rows, $user_id)
    {
        return $this->updateOrInsertManyByField($rows, $user_id, 'ad_id');
    }

    public function updateCreatives($rows, $user_id) {

        $creative_ids = array_column($rows, 'id');
        $ads = $this->find('all')->where([
            'user_id' => $user_id,
            'creative_id IN' => $creative_ids
        ]);

        foreach ($rows as $row)
        {
            $id = $row['id'];
            $grouped[$id] = $row;
        }

        $updated_ads = [];

        // update with data fetched from the API
        foreach ($ads as $ad)
        {
            $creative_id = $ad->creative_id;
            if (isset($grouped[$creative_id]))
            {
                $ad->creative_data = json_encode($grouped[$creative_id]);
                $updated_ads[] = $ad;
                unset($grouped[$id]);
            }
        }

        // save to database
        $this->connection()->transactional(function () use ($updated_ads) {
            foreach ($updated_ads as $ad)
            {
                $this->save($ad, ['atomic' => false]);
            }
        });
    }


    public function getAd($user_id, $ad_id)
    {
        return $this->find()->where([
            'user_id' => $user_id,
            'ad_id' => $ad_id
        ])->first();
    }


    public function getAdIds($user_id, $account_id)
    {
        return $this->find('all')
            ->select(['ad_id'])
            ->where([
                'user_id' => $user_id,
                'account_id' => $account_id
            ])->extract('ad_id')->toArray();
    }


    public function getAll($user_id, $account_id, $campaign_id = 'all')
    {
        $query = $this->find()->select([
            'account_id', 'campaign_id', 'adset_id', 'ad_id',
            'name', 'bid_amount', 'creative_data'
        ]);

        $query->where([
            'user_id' => $user_id,
            'account_id' => $account_id,
            'status IN' => ['ACTIVE', 'PAUSED']
        ]);

        if ($campaign_id != 'all') {
            $query->where(['campaign_id' => $campaign_id]);
        }

        $data = $query->map(function ($row) {
            return $row->toArray();
        })->toArray();

        return $data;
    }

}
