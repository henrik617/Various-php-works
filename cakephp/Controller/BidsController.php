<?php
/**
 * Created by PhpStorm.
 * User: syaron66
 * Date: 11/8/2015
 * Time: 12:10 PM
 */

namespace App\Controller;

use App\BL\Algos\AlgoBase;
use App\Form\SearchForm;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\I18n\Time;
use Cake\Network\Exception\NotFoundException;
use Cake\View\Exception\MissingTemplateException;
use Cake\ORM\TableRegistry;
use Cake\Event\Event;

use App\BL\ViewObject\DataTable;
use App\BL\Facebook\FacebookAdsWrapper;


class BidsController extends ReportBaseController{

    /* @var  FacebookAdsWrapper $fbConnector*/
    private $fbConnector;

    private $algoList=array (
        'sniper'=>'Sniper (Imp,Installs)',
        'raptor'=>'Raptor'
    );

    private $factorList = array (
        'COWARD'=>'COWARD (0.5)',
        'BUNKER'=>'BUNKER (0.75)',
        'NORMAL'=>'NORMAL(1)',
        'AGGRESSIVE'=>'AGGRESSIVE (1.25)',
        'PLAYER'=>'PLAYER(1.5)',
        'GREEDY'=>'GREEDY (1.75)',
        'FUCKU'=>'FUCKU (2)');

    private $factorValues = array (
        'COWARD'=>0.5,
        'BUNKER'=>0.75,
        'NORMAL'=>1,
        'AGGRESSIVE'=>1.25,
        'PLAYER'=>1.5,
        'GREEDY'=>1.75,
        'FUCKU'=>2);

    public function beforeFilter (Event $event)
    {



        parent::beforeFilter($event);
        $this->loadModel('AdInsights');
        $this->loadModel('FacebookAdsets');
        $this->loadModel('FacebookAdAccounts');
        $this->loadModel('FacebookInsights');
        $this->loadModel('FacebookCampaigns');



        $token =$this->hasValidToken();
        if (!$token && $this->request->action != 'fb_connect') {
            $this->log('no valid token redirecting to connect', 'debug');
            return $this->redirect('/facebook/fb_connect');
        }


        $this->fbConnector = new FacebookAdsWrapper();
        $this->fbConnector->init($token);

    }

    private function setDateFilter ()
    {
        $from_str = '2015-09-21';
        $to_str = date('Y-m-d',microtime(true));

        $from = $this->request->query('from');
        $from_default = $this->request->query('from-default');

        // a new from date has not been selected
        if ($from_default == $from) {
            // first insight record
            $first = $this->FacebookInsights->firstRecord($this->user->id, $this->selected_account_id);
            $from = $first ?
                $first->request_date->format('m/d/Y') :
                date('m/d/Y', microtime(true) - 90*24*60*60);
        }

        $to = $this->request->query('to') ?: date('m/d/Y', microtime (true));


        $this->request->query['from-default'] = $from;
        $this->request->query['from'] = $from;
        $this->request->query['to'] = $to;


        //new reports style
        $from_str = urldecode($from);
        $to_str = urldecode($to);

        $from_str = date('Y-m-d', strtotime($from_str));
        $to_str = date('Y-m-d', strtotime($to_str));


        $this->from_str = $from_str;
        $this->to_str= $to_str;
        $this->set ('from_str',$this->from_str);
        $this->set ('to_str',$this->to_str);
    }


    private function setCondition ()
    {

    }

    public function bid_maker ()
    {
        $reportTitle ="Bid Maker";

        $ad_accounts = $this->FacebookAdAccounts->getAdAccountOptions($this->user->id,
            ['is_enabled' => true, 'bidder_enabled' => true]);
        $this->set('ad_accounts', $ad_accounts);

        //set ad account from query
        $account_id = $this->request->query('Account');
        if (in_array($account_id, array_keys($ad_accounts))) {
            $this->selected_account_id = $account_id;
        }
        else if ($ad_accounts) {
            $this->selected_account_id = array_keys($ad_accounts)[0];
        }

        $this->setDateFilter();

        $campaign_filters = $this->request->query('only_enabled_campaigns') === '0' ?
            [] : ['status' => 'ACTIVE'];

        $campaigns = $this->FacebookCampaigns->getCampaignOptions(
            $this->user->id, $this->selected_account_id, $campaign_filters);

        $campaign_options = ['all' => 'ALL'] + $campaigns;
        $this->set('campaigns', $campaign_options);
        $this->selected_campaign_id = $this->request->query('campaign') ?: 'all';

        $searchform = new SearchForm();
        if($this->request->is('get')){
            $this->request->data = $this->request->query;
            if($searchform->execute($this->request->data)){
                #search here
            }
        }


        $hasData=isset($this->request->data['algo'])?true:false;
        $algo=isset($this->request->data['algo'])?$this->request->data['algo']:'sniper';

        $this->algoBid($algo);

        $this->viewBuilder()->layout('sb_admin');
        $this->set ('enabled','true');
        $this->set ('enableExecute',$hasData);
        $this->set ('factorList',$this->factorList);
        $this->set ('algoList',$this->algoList);


        $this->set (compact(
            'searchform','reportTitle',
            'reportTypes'
        ));

    }


    private function algoBid($type='sniper')
    {
        $cond = !empty($this->request->data['condition'])?"Where " . $this->request->data['condition']:null;
        $factor=empty($this->request->data['factor'])?null:$this->request->data['factor'];
        $factorMult=isset($this->factorValues[$factor])?$this->factorValues[$factor]:1;

        $bidder = AlgoBase::create($type);

        $rows = $bidder->calculate([
            'user_id'=>$this->user->id,
            'account_id'=>$this->selected_account_id,
            'campaign_id'=>$this->selected_campaign_id,
            'from'=>$this->from_str,
            'to'=>$this->to_str,
            'cond'=>$cond,
            'multiplier'=>$factorMult
        ],[]);



        $sumsDataObj= new DataTable(array());
        $tableDataObj=new DataTable($rows);

        //debug ($tableDataObj);
        $this->set (compact('tableDataObj','sumsDataObj'));
    }


    private function publishBids ()
    {

        $this->bulk_update();
        //$tableDataObj =  $this->fbConnector->setAdsetBidData($this->account,'6034188211038',"OCPM",'1.69');


    }

    public function bulk_update ()
    {
        $body = $this->request->data;

        if (!isset($this->request->data['data']))
        {
            $this->returnResponse(false,'Invalid request',null);
        }

        $account = $this->request->data('Account');
        if ($account == null) {
            $this->returnResponse(false,'Invalid request',null);
        }

        $account_num = 'act_'.$account;

        $adsets_data= json_decode($this->request->data['data'],true);
        $total_requested_changes = count($adsets_data);

        $this->log ('request for changing bids: ' . $account_num);
        $this->log ('request changes for adsets: ' . $total_requested_changes );

        //for debuging
//        if (
//        (!isset ($this->request->data['campaign']) ||($this->request->data['campaign']!='6034188205838')) ||
//        ($total_requested_changes!=5)
//        )
//        {
//            $this->log ("invalid campaign num while debugging!!!!");
//            $this->returnResponse(false,'invalid campaign num while debugging',null);
//            exit;
//        }



        if ($total_requested_changes<=0)
        {
            $this->returnResponse(false,'NO changes were requested',null);
            exit;
        }



        $results=array ();
        $must_have =array ('adset_id','new_optimization_goal','new_billing_event','new_bid','new_budget');
        $updated=0;
        $index=0;
        foreach ($adsets_data as $adset)
        {
            $index++;
            $fields_valid=true;
            foreach ($must_have as $field)
            {
                if (!isset ($adset[$field]))
                {
                    $results[]=array ('msg'=>"missing field $field",'status'=>false);
                    $this->log ("missing field $field");
                    $fields_valid=false;
                    continue;
                }
            }

            if (!$fields_valid) continue;


            $is_ok=false;
            $msg="";


            $adset['new_bid']= floatval($adset['new_bid']);

            if ($adset['new_bid']<=0.05)
            {
                $msg="NO Change - Bid is to low < 0.05";
            }
            else
            {
//               $adset['new_budget']=101;
                // $this->log ('new budget ' . $adset['new_budget']);

                $is_ok = $this->fbConnector->setAdsetBidData (
                    $account_num,
                    $adset['adset_id'],
                    $adset['new_optimization_goal'],
                    $adset['new_billing_event'],
                    $adset['new_bid'],
                    $adset['new_budget']
                );
            }



            if ($is_ok)
            {
                $this->log ("updated adset with new bid $index/$total_requested_changes " . $adset['adset_id'],'debug');
                $msg='Changed Bid To: ' .$adset['new_bid'];
                $updated++;
            }
            else
            {
                $this->log ("could NOT update adset " . $adset['adset_id'],'debug');
                $msg="could NOT update adset " . $adset['adset_id'];
            }

            $results[]=array (
                'status'=>$is_ok,
                'adset_id'=>$adset['adset_id'],
                'optimization_goal'=>$adset['new_optimization_goal'],
                'billing_event'=>$adset['new_billing_event'],
                'bid'=>$adset['new_bid'],
                'budget'=>$adset['new_budget'],
                'text'=>$msg,
            );


        }

        //updating DB
        foreach ($results as $adset_data)
        {
            if (!$adset_data['status']) continue;
//            $status = $adset_data['status'];
            unset($adset_data['status']);
            $this->FacebookAdsets->updateOrAddAdset ($adset_data);

        }

        $this->returnResponse(true,"Updated $updated/$total_requested_changes Adsets",array ('new_bids'=>$adsets_data,'results'=>$results));

    }

    private function returnResponse ($status,$text,$body)
    {

        $res = array (
            'status'=>$status,
            'time'=>new Time (date('Y-m-d H:i:s',microtime(true))),
            'msg'=> array ('text'=>$text,'body'=>$body)
        );
        echo json_encode($res);
        exit;
    }

    private function job_ready ()
    {

    }


}
