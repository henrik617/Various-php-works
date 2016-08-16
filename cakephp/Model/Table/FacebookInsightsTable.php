<?php
namespace App\Model\Table;


use App\Model\Entity\AdInsight;
use App\BL\Customization;

use Cake\Datasource\ConnectionManager;
use Cake\I18n\Time;
use Cake\Log\Log;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;


/**
 * FacebookInsights Model
 */
class FacebookInsightsTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('ad_insights');
        $this->addBehavior('Timestamp');
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
            ->allowEmpty('adgroup_name');

        $validator
            ->allowEmpty('impressions');

        $validator
            ->allowEmpty('clicks');

        $validator
            ->allowEmpty('reach');

        $validator
            ->add('frequency', 'valid', ['rule' => 'decimal'])
            ->allowEmpty('frequency');

        $validator
            ->add('spend', 'valid', ['rule' => 'decimal'])
            ->allowEmpty('spend');

        $validator
            ->allowEmpty('fb_mobile_purchase');

        $validator
            ->add('request_date', 'valid', ['rule' => 'datetime'])
            ->allowEmpty('request_date');

        $validator
            ->add('date_start', 'valid', ['rule' => 'datetime'])
            ->allowEmpty('date_start');

        $validator
            ->add('date_stop', 'valid', ['rule' => 'datetime'])
            ->allowEmpty('date_stop');

        $validator
            ->allowEmpty('country');

        $validator
            ->add('e_registrations', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('e_registrations');

        $validator
            ->add('e_ftd', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('e_ftd');

        $validator
            ->add('e_house_win', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('e_house_win');

        $validator
            ->allowEmpty('e_dp');

        $validator
            ->allowEmpty('e_source');

        $validator
            ->allowEmpty('e_manager');

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
        return $rules;
    }



    private function campaginClause($campaign_id, $table_name = '')
    {
        if ($campaign_id != 'all') {
            $id = intval($campaign_id);
            return !empty($table_name)
                ? $table_name.'.campaign_id='.$id
                : 'campaign_id='.$id;
        }
        else {
            return '1=1';
        }
    }

    private function prependComma($str)
    {
        return !empty($str) ? ','.$str: '';
    }

    public function firstRecord($user_id, $account_id)
    {
        return $this
            ->find('all', ['order' => ['request_date' => 'ASC']])
            ->where([
                'user_id' => $user_id,
                'account_id' => $account_id
            ])->first();
    }

    public function summary ($user_id, $account_id, $campaign_id,$from,$to)
    {
        $connection = ConnectionManager::get('default');
        $campaign_clause = $this->campaginClause($campaign_id);
        $custom = new Customization($user_id, $account_id, $campaign_id);
        $additional = $this->prependComma($custom->getSqlColumns());

        $rows=$connection->execute("
           select
                sum(spend) as Spend
                $additional
            from ad_insights
            where user_id = :user_id and account_id = :account_id
                and request_date >= :from and request_date <= :to
                and $campaign_clause
        ", [
            'user_id' => $user_id,
            'account_id' => $account_id,
            'from' => $from,
            'to' => $to
        ])->fetchAll('assoc');

        return $rows;
    }


    // summary?
    public function getOverview($user_id, $account_id, $from='', $to='')
    {
        $all_time = ($from == '' or $to == '');
        $connection = ConnectionManager::get('default');
        $custom = new Customization($user_id, $account_id, $campaign_id);
        $additional = $this->prependComma($custom->getSqlColumns());

        $sql = "
           select
                round(sum(spend),2) as Spend,
                sum(impressions) as Impressions,
                sum(clicks) as Clicks,
                round (sum(spend)/sum(clicks),2) as CPC
                $additional
            from ad_insights
            where user_id = :user_id and account_id = :account_id
            ";

        if (!$all_time) $sql .= "and request_date >= :from and request_date <= :to";

        $params = $all_time ?
            compact('user_id', 'account_id') :
            compact('user_id', 'account_id', 'from', 'to');

        $rows = $connection->execute($sql, $params)->fetch('assoc');
        return $rows;
    }


    public function perDay ($user_id, $account_id, $campaign_id,$from,$to)
    {

        $connection = ConnectionManager::get('default');
        $campaign_clause = $this->campaginClause($campaign_id);
        $custom = new Customization($user_id, $account_id, $campaign_id);
        $additional = $this->prependComma($custom->getSqlColumns());



        $rows=$connection->execute("
           select
                date(request_date) as Date,
                dayname(request_date) as DOW,
                round(sum(spend),2) as Spend,
                sum(impressions) as Impressions,
                sum(clicks) as Clicks,
                round (sum(spend)/sum(clicks),2) as CPC
                $additional
            from ad_insights
            where user_id = :user_id and account_id = :account_id
                and request_date >= :from and request_date <= :to
                and $campaign_clause
            group by request_date
            order by request_date
        ", [
            'user_id' => $user_id,
            'account_id' => $account_id,
            'from' => $from,
            'to' => $to
        ])->fetchAll('assoc');



        return $rows;

    }


    public function byCampaign ($user_id, $account_id,$campaign_id,$from,$to)
    {
        $connection = ConnectionManager::get('default');
        $custom = new Customization($user_id, $account_id, $campaign_id);
        $additional = $this->prependComma($custom->getSqlColumns());

        $rows=$connection->execute("
           select
                campaign_name as 'Campaign Name',
                campaign_id as 'Campaign ID',
                sum(spend) as Spend,
                sum(impressions) as Impressions,
                sum(clicks) as Clicks,
                round (sum(spend)/sum(clicks),2) as CPC
                $additional

            from ad_insights
            where user_id = :user_id
                and account_id = :account_id
                and request_date >= :from
                and request_date <= :to
            group by campaign_id
            order by request_date DESC
        ",[
            'user_id' => $user_id,
            'account_id' => $account_id,
            'from' => $from,
            'to' => $to
        ])->fetchAll('assoc');

        return $rows;

    }


    public function ByAdset ($user_id, $account_id, $campaign_id,$from,$to)
    {
        $connection = ConnectionManager::get('default');
        $campaign_clause = $this->campaginClause($campaign_id, 'ad_insights');
        $custom = new Customization($user_id, $account_id, $campaign_id);
        $additional = $this->prependComma($custom->getSqlColumns());

        $query ="
            select
              campaign_name,
              adset_name,
              sum(spend) as Spend,
              sum(impressions) as Impressions,
              sum(clicks) as Clicks,
              facebook_adsets.bid as 'BID'
              $additional

            from ad_insights
                left join facebook_adsets on facebook_adsets.adset_id=ad_insights.adset_id

            where ad_insights.user_id = :user_id
                and ad_insights.account_id = :account_id
                and request_date >= :from and request_date <= :to
                and $campaign_clause
            group by ad_insights.adset_id

        ";

        $rows=$connection->execute($query, [
            'user_id' => $user_id,
            'account_id' => $account_id,
            'from' => $from,
            'to' => $to
        ])->fetchAll('assoc');
        return $rows;
    }


    public function byCountry ($user_id, $account_id, $campaign_id,$from,$to)
    {
        $connection = ConnectionManager::get('default');
        $campaign_clause = $this->campaginClause($campaign_id);
        $custom = new Customization($user_id, $account_id, $campaign_id);
        $additional = $this->prependComma($custom->getSqlColumns());

        $query ="
            select
                country as Country,
                sum(spend) as Spend,
                sum(impressions) as Impressions,
                sum(clicks) as Clicks
                $additional
            from ad_insights
            where user_id = :user_id and account_id = :account_id
                and request_date >= :from
                and request_date <= :to
                and country!='ALL' and country!='NOATTR'
                and $campaign_clause
            group by Country
        ";

        $rows=$connection->execute($query, [
            'user_id' => $user_id,
            'account_id' => $account_id,
            'from' => $from,
            'to' => $to
        ])->fetchAll('assoc');

        //now fetching just no attr with backend stats
        $query ="
            select
                country as Country,
                sum(spend) as Spend,
                sum(impressions) as Impressions,
                sum(clicks) as Clicks
                $additional
            from ad_insights
            where user_id = :user_id and account_id = :account_id
                and request_date >= :from and request_date <= :to and country='NOATTR'
                and $campaign_clause
            group by Country
        ";

        $rows1=$connection->execute($query, [
            'user_id' => $user_id,
            'account_id' => $account_id,
            'from' => $from,
            'to' => $to
        ])->fetchAll('assoc');

        $rows[]=$rows1[0];

        return $rows;
    }


    public function ByAdGroup ($user_id, $account_id, $campaign_id,$from,$to)
    {
        $connection = ConnectionManager::get('default');
        $campaign_clause = $this->campaginClause($campaign_id);
        $custom = new Customization($user_id, $account_id, $campaign_id);
        $additional = $this->prependComma($custom->getSqlColumns());

        $query ="
            select
              campaign_name,
              adgroup_id,
              adset_name,
              adgroup_name,
              sum(impressions) as Impressions,
              sum(spend) as Spend,
              sum(clicks) as Clicks
              $additional

            from ad_insights
            where user_id = :user_id
                and account_id = :account_id
                and request_date >= :from
                and request_date <= :to
                and $campaign_clause
            group by adgroup_id
        ";

        $rows=$connection->execute($query, [
            'user_id' => $user_id,
            'account_id' => $account_id,
            'from' => $from,
            'to' => $to
        ])->fetchAll('assoc');
        return $rows;
    }

    public function ByDow ($user_id, $account_id, $campaign_id,$from,$to)
    {
        $connection = ConnectionManager::get('default');
        $campaign_clause = $this->campaginClause($campaign_id);

        $custom = new Customization($user_id, $account_id, $campaign_id);
        $additional = $this->prependComma($custom->getSqlColumns());

        $rows=$connection->execute("
           select
                DAYOFWEEK(request_date) as i,
                dayname(request_date) as DOW,
                sum(spend) as Spend,
                sum(impressions) as Impressions
                $additional

            from ad_insights
            where user_id = :user_id
                and account_id = :account_id
                and request_date >= :from
                and request_date <= :to
                and $campaign_clause
            group by DOW
            order by DAYOFWEEK(request_date) DESC

        ", [
            'user_id' => $user_id,
            'account_id' => $account_id,
            'from' => $from,
            'to' => $to
        ])->fetchAll('assoc');

        return $rows;
    }

    public function byCreative($user_id, $account_id, $campaign_id,$from,$to)
    {
        $connection = ConnectionManager::get('default');
        $campaign_clause = $this->campaginClause($campaign_id);
        $custom = new Customization($user_id, $account_id, $campaign_id);
        $additional = $this->prependComma($custom->getSqlColumns());

        $query ="
            select
              REPLACE(
              REPLACE(
                REPLACE(adgroup_name,'(1)',''),
                 '(2)',''),'(3)','')

              as name,
              sum(spend) as Spend,
              sum(impressions) as Impressions,
              sum(clicks) as Clicks

              $additional,

              round (sum(installs)/sum(impressions),4) as 'Inst%Imp',
              round (sum(e_registrations)/sum(impressions),4) as 'Reg%Imp',
              round (sum(e_ftd)*1000/sum(impressions),6) as 'FTDM%Imp'

            from ad_insights
            where user_id = :user_id and account_id = :account_id
                and request_date >= :from and request_date <= :to
                and $campaign_clause
            group by name

        ";


        $rows=$connection->execute($query, [
            'user_id' => $user_id,
            'account_id' => $account_id,
            'from' => $from,
            'to' => $to
        ])->fetchAll('assoc');
        return $rows;
    }

    public function topAdsets($user_id, $account_id, $campaign_id,$from,$to)
    {
        $connection = ConnectionManager::get('default');
        $campaign_clause = $this->campaginClause($campaign_id);
        $custom = new Customization($user_id, $account_id, $campaign_id);
        $additional = $this->prependComma($custom->getSqlColumns());

        $rows=$connection->execute("
           select
                campaign_name,
                adset_name,
                sum(spend) as Spend

                $additional

            from ad_insights
            where user_id = :user_id
            and account_id = :account_id
            and request_date >= :from and request_date <= :to
            and $campaign_clause

            group by adset_id
            having  round (sum(spend)/sum(e_ftd),2)<550 and
             sum(e_ftd) >=3
            order by request_date DESC
        ", [
            'user_id' => $user_id,
            'account_id' => $account_id,
            'from' => $from,
            'to' => $to
            ])->fetchAll('assoc');

        return $rows;
    }

    public function botAdsets($user_id, $account_id, $campaign_id,$from,$to)
    {
        $connection = ConnectionManager::get('default');
        $campaign_clause = $this->campaginClause($campaign_id);
        $custom = new Customization($user_id, $account_id, $campaign_id);
        $additional = $this->prependComma($custom->getSqlColumns());


        $rows=$connection->execute("
           select
                campaign_name,
                adset_name,
                sum(spend) as Spend,
                sum(installs) as Installs

                $additional,

                sum(fb_mobile_purchase) as 'FTD(mobile)',
                sum(offsite_conversions) as 'Purchases'

            from ad_insights
            where user_id = :user_id
            and account_id = :account_id
            and request_date >= :from and request_date <= :to
            and $campaign_clause

            group by adset_id
            having
              (round (sum(spend)/sum(e_ftd),2)>1200)
                OR
              (
                sum(spend) > 1000 and sum(e_ftd)=0
              )

            order by request_date DESC
        ", [
            'user_id' => $user_id,
            'account_id' => $account_id,
            'from' => $from,
            'to' => $to
        ])->fetchAll('assoc');

        return $rows;
    }

    public function byLanguage($user_id, $account_id, $campaign_id,$from,$to)
    {
        $connection = ConnectionManager::get('default');
        $campaign_clause = $this->campaginClause($campaign_id);
        $custom = new Customization($user_id, $account_id, $campaign_id);
        $additional = $this->prependComma($custom->getSqlColumns());

        $query ="
                select

                if(
                adset_name like '%*%'=1,
                      SUBSTRING_INDEX (SUBSTRING_INDEX(adset_name, '*', 1),'_',-1) ,
                      SUBSTRING_INDEX (SUBSTRING_INDEX(adset_name, '-', 1),'_',-1)
                  ) as language,


                  sum(spend) as Spend,
                  sum(impressions) as Impressions,
                  sum(clicks) as Clicks

                  $additional

                from ad_insights
                where user_id = :user_id
                and account_id = :account_id
                and request_date >= :from and request_date <= :to
                and $campaign_clause

                group by SUBSTRING_INDEX (SUBSTRING_INDEX(adset_name, '*', 1),'_',-1)
                order by request_date DESC

        ";

        $rows=$connection->execute($query, [
            'user_id' => $user_id,
            'account_id' => $account_id,
            'from' => $from,
            'to' => $to
        ])->fetchAll('assoc');

        return $rows;
    }

    public function byPlatformDevice($user_id, $account_id, $campaign_id,$from,$to)
    {
        $connection = ConnectionManager::get('default');
        $campaign_clause = $this->campaginClause($campaign_id);
        $custom = new Customization($user_id, $account_id, $campaign_id);
        $additional = $this->prependComma($custom->getSqlColumns());

        $query ="
            select

              country as Country,

              if (locate ('Anyoption',campaign_name),'Anyoption','Copyop') as 'Platform',
              if (locate ('iOS',adset_name),'Iphone','Android') as 'Device',
              sum(spend) as Spend,
              sum(impressions) as Impressions,
              sum(clicks) as Clicks

              $additional

            from ad_insights
            where account_id = :account_id
                and user_id = :user_id
                and request_date >= :from and request_date <= :to
                and country!='ALL' and country!='Unknown'
                and $campaign_clause

            group by country,Platform,Device

        ";

        $rows=$connection->execute($query, [
            'user_id' => $user_id,
            'account_id' => $account_id,
            'from' => $from,
            'to' => $to
        ])->fetchAll('assoc');
        //group by SUBSTRING_INDEX (SUBSTRING_INDEX(adset_name, '*', 1),'_',-1)

        foreach ($rows as &$row)
        {
            if ($row['Country']=="NOATTR")
            {
                $row['Platform']='Copyop';
                $row['Device']="Iphone";
            }
        }

        return $rows;
    }


    public function compareDurations($user_id, $account_id, $campaign_id,$from,$to, $compare_from, $compare_to)
    {
        $connection = ConnectionManager::get('default');
        $campaign_clause = $this->campaginClause($campaign_id);
        $custom = new Customization($user_id, $account_id, $campaign_id);
        $additional = $this->prependComma($custom->getSqlColumns());

        $rows1 =$connection->execute("
           select
                :from as `From`,
                :to as `To`,
                round(sum(spend),2) as Spend,
                sum(impressions) as Impressions,
                sum(clicks) as Clicks,
                round (sum(spend)/sum(clicks),2) as CPC
                $additional
            from ad_insights
            where user_id = :user_id and account_id = :account_id
                and request_date >= :from and request_date <= :to
                and $campaign_clause
        ", [
            'user_id' => $user_id,
            'account_id' => $account_id,
            'from' => $from,
            'to' => $to
        ])->fetchAll('assoc');

        $rows2 = $connection->execute("
           select
                :from as `From`,
                :to as `To`,
                round(sum(spend),2) as Spend,
                sum(impressions) as Impressions,
                sum(clicks) as Clicks,
                round (sum(spend)/sum(clicks),2) as CPC
                $additional
            from ad_insights
            where user_id = :user_id and account_id = :account_id
                and request_date >= :from and request_date <= :to
                and $campaign_clause
        ", [
            'user_id' => $user_id,
            'account_id' => $account_id,
            'from' => $compare_from,
            'to' => $compare_to
        ])->fetchAll('assoc');

        return array_merge($rows1, $rows2);
    }
//    ------------ SNIPER ALGOS

    public function findAdsetForBids ($user_id, $account_id, $campaign_id,$from,$to,$cond)
    {

        $connection = ConnectionManager::get('default');

        $custom = new Customization($user_id, $account_id, $campaign_id);
        $additional = $this->prependComma($custom->getSqlColumns());

        $campaign_clause = "";
        if ($campaign_id!="all")
            $campaign_clause =" and ad_insights.campaign_id=$campaign_id";

        $query = "
         select
                    * FROM
                    (
                    select

                      ad_insights.adset_name as Name,
                      ad_insights.adset_id as 'Adset ID',
                      ad_insights.campaign_id,
                      sum(spend) as Spend,
                      sum(impressions) as Impressions,
                      round(facebook_adsets.budget,0) as 'Budget',
                      facebook_adsets.optimization_goal as 'Optimization Goal',
                      facebook_adsets.billing_event as 'Billing Event',
                      facebook_adsets.bid as 'BID'
                      $additional
                    from ad_insights
                    left join facebook_adsets on facebook_adsets.adset_id=ad_insights.adset_id
                    where ad_insights.account_id = :account_id
                        and ad_insights.user_id = :user_id
                        and request_date >= :from and request_date <= :to
                        and campaign_name!='NOATTR'
                        and facebook_adsets.status='ACTIVE'
                        $campaign_clause
                    group by ad_insights.adset_id
                    ) b
                     $cond;
        ";


        //

        //round(sum(spend)/sum(e_registrations),2) as 'CPR',

        Log::write('debug', $query);
        Log::write('debug', print_r (
            [
                'user_id' => $user_id,
                'account_id' => $account_id,
                'from'=> $from,
                'to' => $to
            ],true
        ));


        $rows=$connection->execute($query, [
            'user_id' => $user_id,
            'account_id' => $account_id,
            'from'=> $from,
            'to' => $to
        ])->fetchAll('assoc');
//

        //  debug ($rows[0]);
        return $rows;
    }


    public function sniperBid ($user_id, $account_id, $campaign_id,$from,$to,$cond,$multiplier=1)
    {
        $rows=$this->findAdsetForBids($user_id, $account_id, $campaign_id,$from,$to,$cond);
//        Log::write('debug', 'doing sniper bid');
//        Log::write('debug', print_r ($rows,true));


        $sniperBid = 0;
        foreach ($rows as &$row)
        {

            $custom = new Customization($user_id, $account_id, $row['campaign_id']);
            $algo_settings = $custom->getAlgoSettings ();

            if (!$algo_settings)
                continue;
                // return $rows;

           // print_r ($algo_settings);exit;


    //        $MinimumSpend=50;
    //        $DefaultInstallToFTD=50;
    //        $DefaultBid=15;


            $short_conversion_key=   $algo_settings['short_conversion'];//'Installs';
            $long_conversion_key=$algo_settings['long_conversion'];

    //        print_r ($algo_settings);exit;

            $MAXIMUM_BID = $algo_settings['maximum_bid'];
            $TCPA = $algo_settings['tcpa'];
            $TCPI = $algo_settings['tcpi'];
            $billing_event=$algo_settings['billing_event'];
            $optimization_goal=$algo_settings['optimization_goal'];


            $sniperBid = 0;

            $maxed_ftd=0;
            if (isset ($row['FTD(fb)'])) //hack for anyoption where data is also comming from there backend
                $maxed_ftd= max (intval($row['FTD(fb)']) , intval($row[$long_conversion_key]));
            else
            {
                if (isset($row[$long_conversion_key]))
                    $maxed_ftd =$row[$long_conversion_key];
            }


//            Log::write('debug', "$maxed_ftd");

            $spend = $row['Spend'];

            $installs = intval($row[$short_conversion_key]);
            $cpa=0;
            if ($maxed_ftd>0)
                $cpa = $row['Spend']/$maxed_ftd;

            $cpi=0;
            if ($installs>0)
                $cpi = $row['Spend']/$installs;

            $bid=$row['BID'];

            Log::write('debug', "$maxed_ftd $cpa $cpi");

            $pcpa=0;
            $pcpi=0;
            if ($maxed_ftd==0)
            {
                if ($installs==0)
                {
                    $pcpa=$TCPA+$spend;
                    $pcpi=$TCPI+$spend;
                } else
                {
                    $pcpa=$TCPA+$spend;
                    $pcpi=$cpi;
                }
            }
            else
            {
                $pcpa=$cpa;
                $pcpi=$cpi;
            }

            if ($pcpa==0) $pcpa=$TCPA*2;
            if ($pcpi==0) $pcpi=$TCPI*2;


//            Log::write('error', "bid:$bid");
//            Log::write('error', "PCPA:$pcpa PCPI:$pcpi");

//            if ($spend<$MinimumSpend)
//            {
//                $sniperBid=$DefaultBid;
//            }
//            else
//            {

            if ($maxed_ftd==0)
            {
                if ($spend <$TCPA)
                {
                    $tempbid1=$pcpi*1.1;
                    $tempbid2=$TCPI*2;
                    $sniperBid = min($tempbid1,$tempbid2);
                }
                else
                {
                    $tempbid1=($TCPA/$pcpa)*$pcpi;
                    $tempbid2=$TCPI*3;
                    $sniperBid = min($tempbid1,$tempbid2);
                }
            }
            else
                if ($maxed_ftd<2)
                {
                    $tempbid1=($TCPA/$pcpa)*$pcpi;
                    $tempbid2=$TCPI*3;
                    $sniperBid = min($tempbid1,$tempbid2);
                }
                else
                {
                    $sniperBid=($TCPA/$pcpa)*$pcpi;
                    Log::write('debug', "$TCPA  $pcpa $pcpi");
                }

            $sniperBid*=$multiplier;

            if ($sniperBid>$MAXIMUM_BID) $sniperBid=$MAXIMUM_BID;



            $row['SNIPER CPI BID']=number_format((float)$sniperBid, 2, '.', '');
            $row['New Budget']='105';
            $row['New Billing Event']=$billing_event;
            $row['New Optimization Goal']=$optimization_goal;
            $row['New Bid']=number_format((float)$sniperBid, 2, '.', '');
            $row['Diff']=number_format((float)($sniperBid - $bid), 2, '.', '');
        }

        return $rows;

    }


    // Newly added function for the external add 2016.7.11
    public function addExternalContent($row)
    {
        //todo check if extenal for that date exist
        // $res = $this->find('all')->where(
        //     [
        //         'request_date' => new Time ($row['date']),
        //         'account_id'=>$row['account_id'],
        //         'user_id'=>$row['user_id'],
        //         'col_type'=>'E'
        //     ]
        // )->first();

        // //end check

        // $newRow = $row;
        $newRow = array();
        $newRow['user_id']=$row['user_id'];
        $newRow['account_id'] = $row['account_id'];
        $newRow["adgroup_id"] = $row['adgroup_id'];
        $newRow['e_ftd']=$row['ftd'];
        $newRow['e_registrations']=$row['registrations'];
        $newRow['date_stop'] = $row['dates'];
        $newRow['date_start'] = $row['dates'];
        $newRow['request_date'] = $row['dates'];
        $newRow['created'] = date('Y-m-d H:i:s', microtime(true));
        $newRow['col_type']='E';

        // $insight=null;
        $sql_e = "SELECT * FROM ad_insights WHERE request_date='" . $row['dates'] . "' and account_id='" . $row['account_id'] . "' and user_id='" . $row['user_id'] . "' and col_type='E' limit 1";
        $conn = ConnectionManager::get('default');
        $sql_data_e = $conn->execute($sql_e)->fetchAll('assoc');
        if (sizeof($sql_data_e) != 0)
        {
        	$sql_part = "";
        	foreach($newRow as $key => $value)
        	{
        		$sql_part .= $key . "='$value', ";
        	}
        	$sql_part = substr($sql_part,0,strlen($sql_part)-2);

        	$sql_update = "UPDATE ad_insights SET $sql_part where request_date='" . $row['dates'] . "' and account_id='" . $row['account_id'] . "' and user_id='" . $row['user_id'] . "' and col_type='E'";
        	Log::write('debug', 'updating NOATTR Record for '. $row['dates'] );
        	if(!$stmt = $conn->execute($sql_update))
            {
            	Log::write('debug', 'could not save the record!' . print_r($row));
	            return false;
            }
        }
        else 
        {
        	$sql = "SELECT * FROM ad_insights WHERE adgroup_id='" . $row['adgroup_id'] . "' limit 1";
            $sql_data = $conn->execute($sql)->fetchAll('assoc');
            if (sizeof($sql_data) != 0)		// means there is data in the database
            {
            	$sql2 = "SELECT * FROM ad_insights WHERE request_date='" . $row['dates'] . "' AND adgroup_id='" . $row['adgroup_id'] . "' limit 1";
            	$sql_data2 = $conn->execute($sql2)->fetchAll('assoc');
            	if (sizeof($sql_data2) != 0)	// means there is today's data in the database
            	{
	            	$sql3 = "UPDATE ad_insights SET user_id='" . $newRow["user_id"] . "', account_id='" . $newRow["account_id"] . "', e_ftd='" . $newRow["e_ftd"] . "', e_registrations='" . $newRow["e_registrations"] . "', date_stop='" . $newRow["date_stop"] . "', date_start='" . $newRow["date_start"] . "',created='" . $newRow["created"] . "', col_type='" . $newRow["col_type"] . "'  WHERE request_date='" . $row['dates'] . "' and adgroup_id='" . $row['adgroup_id'] . "'";
	            	Log::write('debug', 'update a record for NOATTR ' . $row['dates']);
	                if(!$stmt = $conn->execute($sql3))
	                {
	                	Log::write('debug', 'could not save the record!' . print_r ($row));
			            return false;
	                }
            	}
            	else
            	{
            		$newRow['date_stop'] = new Time ($row['dates']);
			        $newRow['date_start'] = new Time ($row['dates']);
			        $newRow['request_date'] = new Time ($row['dates']);
			        $newRow['created'] = new Time (date('Y-m-d H:i:s', microtime(true)));
                    $insight = $this->newEntity($newRow);
                    Log::write('debug', 'creating new record with stats=0 ' . $row['dates']);
                    if (!$this->save($insight))
			        {
			            Log::write('debug', 'could not save the record!' . print_r ($insight));
			            return false;
			        }
            	}
            }
            else
            {
            	$newRow['campaign_name'] = "NOATTR";
            	$newRow['date_stop'] = new Time ($row['dates']);
		        $newRow['date_start'] = new Time ($row['dates']);
		        $newRow['request_date'] = new Time ($row['dates']);
		        $newRow['created'] = new Time (date('Y-m-d H:i:s', microtime(true)));
            	$insight = $this->newEntity($newRow);
            	Log::write('debug', 'creating new record for NOATTR ' . $row['dates']);
            	if (!$this->save($insight))
		        {
		            Log::write('debug', 'could not save the record!' . print_r ($insight));
		            return false;
		        }
            }
            // $res1 = $this->find('all')->where(
            //     [
            //         'adgroup_id'=>$row['adgroup_id']
            //     ]
            // )->first();
            // if (!$res1)
            // {
            //     $newRow['campaign_name'] = "NOATTR";
            //     $insight = $this->newEntity($newRow);
            //     Log::write('debug', 'creating new record for NOATTR ' . $row['dates']);
            // }
            // else{
            //     $res2 = $this->find('all')->where(
            //         [
            //             'adgroup_id'=>$row['adgroup_id'],
            //             'request_date'=>$row['request_date']
            //         ]
            //     )->first();
            //     if (!$res2)
            //     {
            //         $newRow['account_name'] = "";
            //         $newRow['campaign_name']='';
            //         $newRow['adset_name']='';
            //         $newRow['adgroup_name']='';
            //         $newRow['country']='';
            //         $insight = $this->newEntity($newRow);
            //         Log::write('debug', 'creating new record with stats=0 ' . $row['dates']);
            //     }
            //     else{
            //         $insight = $this->patchEntity($res2,$newRow);
            //         Log::write('debug', 'update a record for NOATTR ' . $row['dates']);
            //     }
            // }
            //if no record then create new
            // $insight = $this->newEntity($newRow);
            // Log::write('debug', 'creating new record for NOATTR ' . $row['dates']);
            //debug ($insight);exit;
        }

        return true;
    }

    // Newly added function for the external bulkUpdate 2016.7.12
    public function bulkUpdateExternalContent($data)
    {
        //todo check if extenal for that date exist
    	$length = sizeof($data);
        $new_data = array();
        for ($i=0;$i<$length;$i++)
        {
            $found = false;
            $new_data_length = sizeof($new_data);
            for ($j=0;$j<$new_data_length;$j++)
            {
                if ($new_data[$j]->date == $data[$i]->date && $new_data[$j]->facebook_ad_id == $data[$i]->facebook_ad_id)
                {
                    $new_data[$j]->ftd = (int)$data[$i]->ftd + (int)$new_data[$j]->ftd;
                    $found = true; break;
                }
            }
            if ($found == false)
                $new_data[] = $data[$i];
        }
        $new_data_length = sizeof($new_data);
        $conn = ConnectionManager::get('default');
        for ($i=0;$i<$new_data_length;$i++)
        {
            $sql = "SELECT * FROM ad_insights WHERE request_date='" . $new_data[$i]->date . "' and adgroup_id='" . $new_data[$i]->facebook_ad_id . "' limit 1";
            $sql_data = $conn->execute($sql)->fetchAll('assoc');

            if (sizeof($sql_data) != 0)
            {
                $e_ftd = (int)$new_data[$i]->ftd + (int)$sql_data[0]["e_ftd"];
                $sql = "UPDATE ad_insights SET e_ftd='$e_ftd' WHERE request_date='" . $new_data[$i]->date . "' and adgroup_id='" . $new_data[$i]->facebook_ad_id . "' LIMIT 1";
                $stmt = $conn->execute($sql);
                Log::write('debug', 'updating Record for '. $new_data[$i]->date );
            }
            else
            {  
                // $given_time = new Time ($new_data[$i]->date);
                $newRow = array();
                $newRow['user_id'] = $new_data[$i]->user_id;
                $newRow['account_id'] = $new_data[$i]->facebook_account_id;
                $newRow["adgroup_id"] = $new_data[$i]->facebook_ad_id;
                $newRow['e_ftd'] = $new_data[$i]->ftd;
                $newRow['e_registrations']= $new_data[$i]->register;
                $newRow['date_stop'] = $new_data[$i]->date;
                $newRow['date_start'] = $new_data[$i]->date;
                $newRow['request_date'] = $new_data[$i]->date;
                $newRow['created'] = date('Y-m-d H:i:s', microtime(true));
                $newRow['col_type']='E';

                $sql = "INSERT INTO ad_insights (user_id,account_id,adgroup_id,e_ftd,e_registrations,date_stop,date_start,request_date,created,col_type) VALUES ('" . $newRow['user_id'] . "','"
                       . $newRow['account_id'] . "','"
                       . $newRow['adgroup_id'] . "','"
                       . $newRow['e_ftd'] . "','"
                       . $newRow['e_registrations'] . "','"
                       . $newRow['date_stop'] . "','"
                       . $newRow['date_start'] . "','"
                       . $newRow['request_date'] . "','"
                       . $newRow['created'] . "','"
                       . $newRow['col_type'] . "')";
                $stmt = $conn->execute($sql);
                Log::write('debug', 'creating new record ' . $new_data[$i]->date);
            }
        }
        return true;
    }

}

