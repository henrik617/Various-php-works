<?php
namespace App\Model\Table;

use App\Model\Entity\FacebookAdset;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * FacebookAdsets Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Adsets
 * @property \Cake\ORM\Association\BelongsTo $Campaigns
 * @property \Cake\ORM\Association\BelongsTo $Accounts
 */
class FacebookAdsetsTable extends Table
{
    use TableTrait;

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('facebook_adsets');
        $this->displayField('id');
        $this->primaryKey('id');
        $this->belongsTo('Adsets', [
            'foreignKey' => 'adset_id'
        ]);
        $this->belongsTo('Campaigns', [
            'foreignKey' => 'campaign_id'
        ]);
        $this->belongsTo('Accounts', [
            'foreignKey' => 'account_id'
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->allowEmpty('id', 'create');

        $validator
            ->add('bid', 'valid', ['rule' => 'decimal'])
            ->allowEmpty('bid');

        $validator
            ->allowEmpty('bid_type');

        $validator
            ->add('budget', 'valid', ['rule' => 'decimal'])
            ->allowEmpty('budget');

        $validator
            ->allowEmpty('auto_bid');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
//        $rules->add($rules->existsIn(['adset_id'], 'Adsets'));
//        $rules->add($rules->existsIn(['campaign_id'], 'Campaigns'));
//        $rules->add($rules->existsIn(['account_id'], 'Accounts'));
        return $rules;
    }

//    public function updateAdset ($adsets_bid_info)
//    {
//
////        debug ($adsets_bid_info);exit;
//
//        $is_ok =    $this->updateAll(
//            [
//                'bid' => $adsets_bid_info['bid'],
//                'budget' => $adsets_bid_info['budget'],
//                'pacing' => $adsets_bid_info['pacing'],
//                'auto_bid' => $adsets_bid_info['auto_bid'],
//            ], // fields
//            ['adset_id' => $adsets_bid_info['id'] ]); // conditions
//
//        return $is_ok;
//
//
//
//    }

    public function addAdset ($adset)
    {
        $newRow = $this->newEntity($adset);
        return $this->save($newRow);
    }

    public function updateOrAddAdset ($adset_info)
    {
       // debug ($adset_info);

        $adset = $this->find('all')->where(
            [
                'adset_id'=>$adset_info['adset_id'],
            ]
        )->first();


        if (!$adset)
        {
            //$this->addAdset($adset);
            $adset = $this->newEntity($adset_info);
        }
        else
        {
            $adset = $this->patchEntity($adset,$adset_info);
        }

        $is_ok =  $this->save($adset);

        if (!$is_ok)
        {
//            debug ($adset_info);
//            exit;
            return false;
        }
        return true;
    }


    public function updateOrInsertMany($rows, $user_id)
    {
        return $this->updateOrInsertManyByField($rows, $user_id, 'adset_id');
    }

    public function getAdsetCount($user_id, $account_id, $campaign_id)
    {
        return $this->find()->where([
            'user_id' => $user_id,
            'account_id' => $account_id,
            'campaign_id' => $campaign_id
        ])->count();
    }


    public function getAdset($user_id, $adset_id)
    {
        return $this->find()->where([
            'user_id' => $user_id,
            'adset_id' => $adset_id
        ])->first();
    }

    public function getAdsetIds($user_id, $account_id = null)
    {
        $query = $this->find('all')
            ->select(['adset_id'])
            ->where(['user_id' => $user_id]);

        if ($account_id != null) {
            $query->where(['account_id' => $account_id]);
        }
        return $query->extract('adset_id')->toArray();
    }

    public function getAll($user_id, $account_id, $campaign_id = 'all')
    {
        $query = $this->find()->select([
            'account_id', 'campaign_id', 'adset_id',
            'budget', 'bid', 'auto_bid', 'status', 'billing_event',
            'optimization_goal', 'name'
        ]);

        $query->where([
            'user_id' => $user_id,
            'account_id' => $account_id
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
