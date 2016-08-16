<?php
namespace App\Controller;

use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Network\Exception\NotFoundException;
use Cake\View\Exception\MissingTemplateException;
use Cake\ORM\TableRegistry;
use Cake\Event\Event;


use FacebookAds\Api;
use Facebook\Facebook;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;

use FacebookAds\Object\AdAccount;
use FacebookAds\Object\Fields\AdAccountFields;
use FacebookAds\Object\Fields\AdCampaignFields;
use FacebookAds\Object\Fields\AdSetFields;
use FacebookAds\Object\AdUser;
use FacebookAds\Object\AdCampaign;
use FacebookAds\Object\Fields\AdGroupFields;
use FacebookAds\Object\AdGroup;
use FacebookAds\Object\Fields\InsightsFields;
use FacebookAds\Object\Insights;
use FacebookAds\Object\Values\InsightsActionBreakdowns;
use FacebookAds\Object\Values\InsightsLevels;
use FacebookAds\Object\Values\InsightsOperators;
use FacebookAds\Object\Values\InsightsPresets;
use FacebookAds\Object\AdSet;
use App\BL\ViewObject\DataTable;
use App\BL\Facebook\FacebookAdsWrapper;

class FacebookController extends AppController{


    public function beforeFilter (Event $event)
    {

        $this->log ("Facebook Before Filter start: ". $this->request->action);


        parent::beforeFilter($event);
        $this->Auth->allow(['fb_connect','fb_connected']);
        if (!$this->hasValidToken() && ($this->request->action!='fb_connect' || $this->request->action!='fb_connected'))
        {
            $this->log ('no valid token redirecting to connect');
            //return $this->redirect('/facebook/fb_connect');
        }




        //init API
        Api::init(
            '1498322540487595',
            '77367f1002752fc852cc476e6c1d6b0f',
            $_SESSION['facebook_access_token']
        );


        $from_str='2015-09-10';
        $to_str = "'". date('Y-m-d',microtime(true)). "'";

        if (isset ($this->request->query['from']) && isset ($this->request->query['to']))
        {
            $from = $this->request->query['from'];
            $to = $this->request->query['to'];

            $from_str = $from['year'] ."-" .$from['month'] . "-" . $from['day'];
            $to_str = $to['year'] ."-" .$to['month'] . "-" . $to['day'];
            $from_str = "'" . $from_str . "'";
            $to_str = "'" . $to_str . "'";
        }

        $this->from_str = $from_str;
        $this->to_str= $to_str;
        $this->set ('from_str',$this->from_str);
        $this->set ('to_str',$this->to_str);

        $this->log ("Facebook Before Filter ends: ". $this->request->action);

    }

    private function hasValidToken ()
    {

        //  return $this->redirect('/facebook/fb_connect');
        $facebookTokens =TableRegistry::get('FacebookTokens');
        $tokensQuery = $facebookTokens->find()->
        where(['user_id' => $this->Auth->user('id')]);

        $tokens =$tokensQuery->toArray();

//        debug ($tokens[0]->access_token);
//        debug ($tokens);exit;

        $tokens=false;

        if (!$tokens)
        {
            $_SESSION['facebook_access_token'] = null;
            return false;
        }

        $_SESSION['facebook_access_token'] = $tokens[0]->access_token;
        return true;
    }



    public function fb_connected ()
    {


        if (!isset($_SESSION['facebook_access_token']))
        {
            echo "error: could not connect to facebook";
            exit;
        }



        $fb = new Facebook([
            'app_id' => '1498322540487595',
            'app_secret' => '77367f1002752fc852cc476e6c1d6b0f',
        ]);

        //replace with long live token
        $accessToken =$_SESSION['facebook_access_token'];
        $oAuth2Client = $fb->getOAuth2Client();
        $longLivedAccessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
        $_SESSION['facebook_access_token']= $longLivedAccessToken->getValue();
        $accessToken =$longLivedAccessToken->getValue();


        //save or update long token into DB
        $response = $fb->get('/me?locale=en_US&fields=name,email',$accessToken);
        $userNode = $response->getGraphUser();
        $userEmail = $userNode->getField('email');

        //check if email exist, update or create new
        $facebookTokens =TableRegistry::get('FacebookTokens');
        $tokenQuery = $facebookTokens->find()->
        where(['email' => $userEmail]);
        $tokenData  = $tokenQuery->toArray();

        if (!$tokenData)
        {
            echo "new token was saved in DB";exit;

            $token = $facebookTokens->newEntity();

            $token->user_id = $this->Auth->user('id');
            $token->access_token = $accessToken;
            $token->email = $userEmail;
            $facebookTokens->save($token);
            echo "new token was saved in DB";
        }
        else //need to update the token
        {

            debug ($tokenData);
            echo "updating new toekn";exit;
            $token_id = $tokenData['FacebookToken'][0]['id'];
            $token = $facebookTokens>get ($token_id);
            $token->access_token = $accessToken;
            $facebookTokens->save($token);
            echo "the token was updated in DB";
        }
        exit;
    }



    public function fb_connect ()
    {
        // session_start();


        $_SESSION['facebook_access_token'] = null;
        $this->log ('fb connect starts');

        $facebookLoginUrl = "http://facebook.pareto.sl/facebook/fb_connected";

        $fb = new Facebook([
            'app_id' => '1498322540487595',
            'app_secret' => '77367f1002752fc852cc476e6c1d6b0f',
        ]);


        if (!isset($_SESSION['facebook_access_token'])) {
            $_SESSION['facebook_access_token'] = null;
        }
        else
        {
          //  return $this->redirect('/facebook');
        }



        $helper=null;
//        if (!$_SESSION['facebook_access_token']) {
//            $this->log ('getting redirect helper');
            $helper = $fb->getRedirectLoginHelper();
//            try {
//                $_SESSION['facebook_access_token'] = (string) $helper->getAccessToken();
//                echo "redirecting to fb_connected";
//                return $this->redirect('/facebook/fb_connected');
//
//            } catch(FacebookResponseException $e) {
//                // When Graph returns an error
//                echo 'Graph returned an error: ' . $e->getMessage();
//                exit;
//            } catch(FacebookSDKException $e) {
//                // When validation fails or other local issues
//                echo 'Facebook SDK returned an error: ' . $e->getMessage();
//                // exit;
//            }
//        }
        $this->log ('access token ' .$_SESSION['facebook_access_token']);
        if (!$_SESSION['facebook_access_token'])
        {
            $this->log ('gettting login url');
            $permissions = ['ads_management','email'];
            $loginUrl = $helper->getLoginUrl($facebookLoginUrl, $permissions);
            echo '<a href="' . $loginUrl . '">Log in with Facebook</a>';
            exit;
        }



        exit;
    }




    public function no_attribution ()
    {
        $this->layout = 'facebook';
        $user = $this->Auth->user ('id');

        $connection = ConnectionManager::get('default');

        $rows=$connection->execute("
SELECT
        dates,sum(ftd),sum(registrations)

    FROM
        `anyoption_trans` `a`

    WHERE
		 ((a.adgroup_id
        not in (select adgroup_id from ad_insights)) or a.adgroup_id is null)
         group by dates

        ")->fetchAll('assoc');


        $tableDataObj=new DataTable($rows);
        $this->set (compact('tableDataObj'));
        $this->render ('/Tables/default');

//        $rows=$connection->execute("


    }

    public function by_country ()
    {
        $this->layout = 'facebook';
        $connection = ConnectionManager::get('default');

        $rows=$connection->execute("
                    select
                      country,
                      sum(spend) as spend,
                      sum(impressions) as impressions,
                      sum(registrations) as registrations,
                      round(sum(spend)/sum(registrations),2) as per_registration,
                      sum(ftd) as ftd,
                      round (sum(spend)/sum(ftd),2) as cpa
                    from anyoption_full_stats
                    where request_date >=$this->from_str and request_date <= $this->to_str
                    group by Country

        ")->fetchAll('assoc');

        $no_attribution=$connection->execute("
          select 'NO-ATTR' as country ,
                 0 as spend,
                 sum(ftd) as ftd,
                 0 as impressions ,
                 sum(registrations) as registrations,
                 0 as per_registration,
                 0 as cpa
          FROM
            `anyoption_trans` `a`
          WHERE
             ((a.adgroup_id
            not in (select adgroup_id from ad_insights)) or a.adgroup_id is null)
            and dates >=$this->from_str and dates <= $this->to_str

        ")->fetchAll('assoc');

        $rows[]=$no_attribution[0];
////        debug ($no_attribution);
//        $last=$rows[count($rows)-1];debug($last);debug ($rows[0]);

        $sums=$connection->execute("
            select
             sum(spend) as spend,
             sum(ftd) as ftd

             from anyoption_full_stats
             where request_date >=$this->from_str and request_date <= $this->to_str

        ")->fetchAll('assoc');

        //if (sum(ftd)>0,sum(spend)/sum(ftd),0) as cpa

        $sums[0]['ftd']+=$no_attribution[0]['ftd'];
        $sums[0]['cpa']=$sums[0]['spend']/$sums[0]['ftd'];
        $sums[0]['cpa']=number_format((float)$sums[0]['cpa'], 2, '.', ''). "$";

//        debug ($sums);

//        and request_date >=$from_str and request_date <= $to_str


        $sumsDataObj = new DataTable($sums);
        $tableDataObj=new DataTable($rows);
        $this->set (compact('tableDataObj','sumsDataObj'));
        $this->render ('/Tables/default');
    }


    public function adsets_all ()
    {
        $this->layout = 'facebook';
        $AdInsights = TableRegistry::get('AdInsights');
        $rows = $AdInsights->adsetStats ($this->from_str,$this->to_str);
        $tableDataObj=new DataTable($rows);
        $this->set (compact('tableDataObj'));
        $this->render ('/Tables/default');
    }


    public function index ()
    {
        $this->layout = 'facebook';
        $user = $this->Auth->user ('id');

        $AdInsights = TableRegistry::get('AdInsights');

        $rows = $AdInsights->statsByDay ();

        $tableDataObj=new DataTable($rows);
        $this->set (compact('tableDataObj'));
        $this->render ('/Tables/default');
    }

    public function ad_accounts ()
    {
        $this->layout = 'facebook';
        $fbConnector = new FacebookAdsWrapper();

        $tableDataObj =  $fbConnector->getAdAccounts();
        $this->set (compact('tableDataObj'));
        $this->render ('/Tables/default');

    }

    public function ad_groups ()
    {
        $this->layout = 'facebook';
        $fbConnector = new FacebookAdsWrapper();

        $tableDataObj =  $fbConnector->getAds('6033434136638');
        $this->set (compact('tableDataObj'));
        $this->render ('/Tables/default');

    }



    public function ad_insights1 ($key)
    {

        $this->layout = 'facebook';
        $CombinedTrans =TableRegistry::get('CombinedTrans');
        $rows = $CombinedTrans->find('all')->
        where(['campaign_id' => $key])->
        toArray();

        $tableDataObj = new DataTable();
        $tableDataObj->setHeader(array(
            "adgroup_name","adset_name","spend","ftd","cpa","net","impressions","clicks","registrations",
            "reach","fb_mobile_purchase","campaign_id","adset_id","house_win"
        ,"adgroup_id"
        ));

        //debug ($rows);exit;

//        $row=array($rows[0]);
//
//        $row = array ($row);
//        debug ($row);

        foreach ($rows as $row)
        {
            $as_arr = array ();
            foreach ($tableDataObj->getHeader() as $fieldName)
            {
                $as_arr[]=$row->{$fieldName};
            }
            $tableDataObj->addRow($as_arr);
        }

        //debug ($tableDataObj);exit;

        $this->set (compact('tableDataObj'));
        $this->render ('/Tables/default');

    }


    public function ad_insights2 ($key)
    {

        $this->layout = 'facebook';

        $from_str='2015-10-01';
        $to_str = "'". date('Y-m-d',microtime(true)). "'";

        if (isset ($this->request->query['from']) && isset ($this->request->query['to']))
        {
            $from = $this->request->query['from'];
            $to = $this->request->query['to'];

            $from_str = $from['year'] ."-" .$from['month'] . "-" . $from['day'];
            $to_str = $to['year'] ."-" .$to['month'] . "-" . $to['day'];
            $from_str = "'" . $from_str . "'";
            $to_str = "'" . $to_str . "'";
        }

        $connection = ConnectionManager::get('default');

        $rows=$connection->execute("
            select
             adset_name,
             adgroup_name,
             sum(spend) as spend,
             sum(ftd) as ftd,
             if (sum(ftd)>0,(sum(ftd)*600) - sum(spend),-1*sum(spend) ) as net600,
             if (sum(ftd)>0,sum(spend)/sum(ftd),null) as cpa,
             if (sum(ftd)>0,(sum(ftd)/sum(impressions))*1000*450   ,null)as max_ocpm_450,
             if (sum(impressions)>0, sum(spend) / (sum(impressions)/1000)  ,null)as cpm,
             sum(registrations) as registrations,
             adgroup_id,
             campaign_id,
             sum(impressions) as impressions,
             sum(clicks) as clicks,
             sum(reach) as reach,

             sum(fb_mobile_purchase) as fb_mobile_purchase,
             sum(house_win) as house_win
            from anyoption_full_stats
            where campaign_id=$key
            and request_date >=$from_str and request_date <= $to_str
            group by adgroup_id
            order by ftd DESC



        ")->fetchAll('assoc');

//        @max_ocpm:=((sum(ftd)/sum(impressions))*1000*450),
//             @cpm:=(sum(spend) / (sum(impressions)/1000)),
//             case
//                 WHEN (sum(ftd) > 0) and (@max_ocpm-@cpm>0) then 'hold'
//                 WHEN (sum(ftd) > 0) and (@max_ocpm-@cpm<0) then 'decrease'
//                 WHEN (sum(ftd) = 0) and (sum(spend)> 1000) then 'decrease'
//                 else 'hold'
//             END as bid_action

        $sums=$connection->execute("
            select sum(ftd) as ftd,
                   sum(spend) as spend,
                   if (sum(ftd)>0,(sum(ftd)*600) - sum(spend),-1*sum(spend) ) as net600,
                   if (sum(ftd)>0,sum(spend)/sum(ftd),0) as cpa
            from anyoption_full_stats
            where campaign_id=$key
            and request_date >=$from_str and request_date <= $to_str
        ")->fetchAll('assoc');




        foreach ($rows as &$row)
        {
            $row['bid_action']="Hold";

            if ($row['ftd'] > 0)
            {
                if ($row['max_ocpm_450'] - $row['cpm'] < 0)
                    $row['bid_action']="Decrease";
            }

            if ($row['ftd'] == 0)
            {
//                if ($row['max_ocpm_450'] - $row['cpm'] < 0)
//                    $row['bid_action']="Decrease";
                if ($row['spend']>1000)
                    $row['bid_action']="Stop";

            }

        }

        $tableDataObj = new DataTable($rows);
        $sumsDataObj = new DataTable($sums);


        $this->set (compact('tableDataObj','from_str','to_str','sumsDataObj'));
        $this->render ('/Tables/default');

    }






    function getCSVData ()
    {
        $file = fopen(WWW_ROOT."uploads/dp_report_by_date_2015-10-01_2015-10-14.csv", 'r') or die("can't open file");
        $header=null;
        $rows = array ();
        $isFirst=true;
        while (($line = fgetcsv($file)) !== FALSE) {

            if ($isFirst)
            {
                $header=$line;
                $isFirst=false;
            }
            else
            {
                array_push($rows,$line);
            }

        }
        fclose($file);
        return array ('header'=>$header,'rows'=>$rows);
    }




    public function sb_table ()
    {
        $this->layout = 'ajax';



        $connection = ConnectionManager::get('default');

        $rows=$connection->execute("
           select dates as date,spend,sum(ftd) as FTD,sum(registrations) as Registrations,
          spend/sum(ftd) as cpa

          from anyoption_trans

          left join (
          select request_date,sum(spend) as spend
from ad_insights
group by request_date
) aa
on dates=request_date
group by dates
order by dates DESC
        ")->fetchAll('assoc');

        $tableDataObj=new DataTable($rows);
        $this->set (compact('tableDataObj'));
//        $this->render ('/Tables/default');
    }


    public function ad_campaigns ()
    {
        $this->layout = 'facebook';
        $fbConnector = new FacebookAdsWrapper();
        $tableDataObj =  $fbConnector->getAdCampaigns('act_963697257021858');
//        $tableDataObj =  $fbConnector->getAdCampaigns('act_757189357695593');
        $this->set (compact('tableDataObj'));
        $this->render ('/Tables/default');
    }

    public function ad_sets ()
    {
        $this->layout = 'facebook';
        $fbConnector = new FacebookAdsWrapper();
        $tableDataObj =  $fbConnector->getAdSets('6034419992238');


        $this->set (compact('tableDataObj'));
        $this->render ('/Tables/default');
    }


    public function ads ()
    {
        $this->layout = 'facebook';
        $fbConnector = new FacebookAdsWrapper();
        $tableDataObj =  $fbConnector->getAds('6034419992238');
        $this->set (compact('tableDataObj'));
        $this->render ('/Tables/default');
    }

}
?>
