<?php


namespace App\Model\Table;


use Cake\ORM\Table;


class FacebookApiStatsTable extends Table
{

    public function updateOrInsertMany($rows, $user_id, $request_date)
    {
        // per account id
        $items = [];
        foreach ($rows as $row) {
            $account_id = $row['account_id'];
            $items[$account_id] = $row;
            $items[$account_id]['user_id'] = $user_id;
        }

        $updated = []; // updated or created items
        $existing = $this->find('all')->where([
            'user_id' => $user_id,
            'request_date' => $request_date,
        ]);

        // update existing items with data fetched from the API
        foreach ($existing as $ex_item)
        {
            $account_id = $ex_item->account_id;
            if (!empty($items[$account_id]))
            {
                $updated[] = $this->patchEntity($ex_item, $items[$account_id]);
                unset($items[$account_id]);
            }
        }

        // insert new data
        foreach ($items as $account_id => $item)
        {
            if (!empty($item)) {
                $updated[] = $this->newEntity($item);
            }
        }

        // save to database
        $this->connection()->transactional(function () use ($updated) {
            foreach ($updated as $item)
            {
                $this->save($item, ['atomic' => false]);
            }
        });
    }


    public function getStats($user_id, $account_id, $request_date)
    {
        return $this->find()->where([
            'user_id' => $user_id,
            'account_id' => $account_id,
            'request_date' => $request_date
        ])->first();
    }

}
