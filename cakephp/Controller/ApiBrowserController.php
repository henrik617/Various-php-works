<?php
/**
 * Created by PhpStorm.
 * User: syaron66
 * Date: 11/8/2015
 * Time: 2:00 PM
 */

namespace App\Controller;


use App\BL\Facebook\FacebookAdsWrapper;
use App\BL\ViewObject\DataTable;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;

class ApiBrowserController extends AppController {


    /* @var  FacebookAdsWrapper $fbConnector*/
    private $fbConnector;

    public function beforeFilter (Event $event)
    {
        parent::beforeFilter($event);

        $token =$this->hasValidToken();
        if (!$token && $this->request->action != 'fb_connect') {
            return $this->redirect('/facebook/fb_connect');
            $this->log('no valid token redirecting to connect', 'debug');
        }


        $this->fbConnector = new FacebookAdsWrapper();
        $this->fbConnector->init($token);


    }

    private function hasValidToken ()
    {

        //  return $this->redirect('/facebook/fb_connect');
        $facebookTokens =TableRegistry::get('FacebookTokens');
        $tokensQuery = $facebookTokens->find()->
        where(['user_id' => $this->Auth->user('id')]);

        $tokens =$tokensQuery->toArray();
        debug ($this->Auth->user('id'));

//        debug ($tokens[0]->access_token);
//        debug ($tokens);exit;

        if (!$tokens)
        {
            $_SESSION['facebook_access_token'] = null;
            echo "cant find valid token, please refetch the token then try again";
          //  exit;
            return false;
        }

        $_SESSION['facebook_access_token'] = $tokens[0]->access_token;
        return $tokens[0]->access_token;
    }

    public function accounts ($key=null)
    {
        $this->set ('reportTitle','Ad Accounts');
        $this->report ($this->request->action,$key);
    }

    public function account ($key)
    {
        $this->set ('reportTitle','Campaigns');
        $this->report ($this->request->action,$key);
    }

    public function campaign ($key)
    {
        $this->set ('reportTitle','Adsets');
        $this->report ($this->request->action,$key);
    }

    public function adset ($key)
    {
        $this->set ('reportTitle','Ads');
        $this->report ($this->request->action,$key);
    }


    public function account_by_hour ($account_num)
    {
        $this->set ('reportTitle','Account Hourly');
        $this->report ($this->request->action,$account_num);
    }


    public function report ($action,$key=null)
    {
        $this->layout = 'sb_admin';

        $tableDataObj=array ();

        if ($action=="accounts") $tableDataObj =  $this->fbConnector->getAdAccounts();
        if ($action=="account") $tableDataObj =  $this->fbConnector->getAdCampaigns("act_" . $key);
        if ($action=="campaign") $tableDataObj =  $this->fbConnector->getAdSets($key,true);
        if ($action=="adset") $tableDataObj =  $this->fbConnector->getAds($key);
        if ($action=="aaccount_by_hourdset") $tableDataObj =  $this->fbConnector->getAds($key);

        $this->addHyperLinks($tableDataObj,$action);


        $this->set (compact('tableDataObj'));
        $this->render ('/Tables/sb_table_simple');
    }


    private function addHyperLinks (DataTable &$dataTable,$action=false)
    {


        $new_header = array ();
        $dictionary = array ();
        $x=0;


        foreach ($dataTable->getHeader() as $field)
        {
            if ($field=="account_id")
                $dictionary['account_id']=$x;

            if ($action=='accounts')
            {
                if ($field=="name")
                    $dictionary['account_name']=$x;
            }

            if ($action=='account')
            {
                if ($field=="name")
                    $dictionary['campaign_name']=$x;
                if ($field=="id")
                    $dictionary['campaign_id']=$x;


            }
            if ($action=='campaign')
            {
                if ($field=="name")
                    $dictionary['adset_name']=$x;
                if ($field=="id")
                    $dictionary['adset_id']=$x;

            }

            if ($action=='adset')
            {
                if ($field=="name")
                    $dictionary['adgroup_name']=$x;
                if ($field=="id")
                    $dictionary['adgroup_id']=$x;
            }

            $new_header[]=Inflector::humanize($field);
            $x++;
        }

//        debug ($new_header);exit;


        $newDataTable = new DataTable();
        $new_body = $dataTable->getRows();
//        debug ($new_body);exit;
        foreach ($new_body as &$row)
        {
            if (isset($dictionary['account_id'])) {
                $id = $row[$dictionary['account_id']];
                $old_val = $row[$dictionary['account_id']];
                $newstr = '<a href="/api_browser/account/' . $id . '">' . $old_val . '</a>';
                $row[$dictionary['account_id']] = $newstr;


                if (isset($dictionary['account_name'])) {
                    $old_val = $row[$dictionary['account_name']];
                    $newstr = '<a href="/api_browser/account/' . $id . '">' . $old_val . '</a>';
                    $row[$dictionary['account_name']] = $newstr;
                }
            }


            if (isset($dictionary['campaign_id'])) {
                $id = $row[$dictionary['campaign_id']];
                $old_val = $row[$dictionary['campaign_id']];
                $newstr = '<a href="/api_browser/campaign/' . $id . '">' . $old_val . '</a>';
                $row[$dictionary['campaign_id']] = $newstr;

                if (isset($dictionary['campaign_name'])) {
                    $old_val = $row[$dictionary['campaign_name']];
                    $newstr = '<a href="/api_browser/campaign/' . $id . '">' . $old_val . '</a>';
                    $row[$dictionary['campaign_name']] = $newstr;
                }
            }

            if (isset($dictionary['adset_id'])) {
                $id = $row[$dictionary['adset_id']];
                $old_val = $row[$dictionary['adset_id']];
                $newstr = '<a href="/api_browser/adset/' . $id . '">' . $old_val . '</a>';
                $row[$dictionary['adset_id']] = $newstr;

                if (isset($dictionary['adset_name'])) {
                    $old_val = $row[$dictionary['adset_name']];
                    $newstr = '<a href="/api_browser/adset/' . $id . '">' . $old_val . '</a>';
                    $row[$dictionary['adset_name']] = $newstr;
                }
            }

            if (isset($dictionary['adgroup_id'])) {
                $id = $row[$dictionary['adgroup_id']];
                $old_val = $row[$dictionary['adgroup_id']];
                $newstr = '<a href="/api_browser/adgroup/' . $id . '">' . $old_val . '</a>';
                $row[$dictionary['adgroup_id']] = $newstr;

                if (isset($dictionary['adgroup_name'])) {
                    $old_val = $row[$dictionary['adgroup_name']];
                    $newstr = '<a href="/api_browser/adgroup/' . $id . '">' . $old_val . '</a>';
                    $row[$dictionary['adgroup_name']] = $newstr;
                }
            }

        }

//        debug ($new_body);exit;

        $dataTable->setHeader($new_header);
        $dataTable->setRows($new_body);

//        $newDataTable->setHeader($new_header);
//
//        $newDataTable->addRows($new_header);

        return $dataTable;

        //return $newDataTable;

    }




} 