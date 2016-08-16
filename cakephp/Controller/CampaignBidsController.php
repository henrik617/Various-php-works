<?php


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
use App\BL\Facebook\FacebookWrapper;


class CampaignBidsController extends ReportBaseController {

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

        $this->loadModel('FacebookTokens');
        $this->loadModel('FacebookAdAccounts');
        $this->loadModel('FacebookCampaigns');
        $this->loadModel('FacebookAdsets');
        $this->loadModel('FacebookInsights');

        // Facebook API
        $app_id = Configure::read('Facebook.app_id');
        $app_secret = Configure::read('Facebook.app_secret');
        $access_token = $this->FacebookTokens->getTokenByUser($this->user->id);
        $this->fbWrapper = new FacebookWrapper($app_id, $app_secret, $access_token);



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



    public function bid_maker ()
    {
        $reportTitle ="Bid Maker";
        $ad_accounts = $this->FacebookAdAccounts->getAdAccountOptions(
            $this->user->id, $filters = ['is_enabled' => true]);
        $this->set('ad_accounts', $ad_accounts);

        //set ad account from query
        $account_id = $this->request->query('Account');
        if (array_key_exists($account_id, $ad_accounts)) {
            $this->selected_account_id = $account_id;
        }
        else if ($ad_accounts) {
            $this->selected_account_id = array_keys($ad_accounts)[0];
        }
        $this->setDateFilter();

        $campaign_filters = $this->request->query('only_enabled_campaigns') === '0' ?
            [] : ['status' => 'ACTIVE'];

        $campaigns = $this->FacebookCampaigns->getAll(
            $this->user->id, $this->selected_account_id, 'all', $campaign_filters);

        foreach ($campaigns as $key => $campaign) {
            $count = $this->FacebookAdsets->getAdsetCount($this->user->id, $account_id, $campaign['campaign_id']);
            $campaigns[$key]['adset_count'] = $count;
        }
        $this->set('campaigns', $campaigns);


        $searchform = new SearchForm();
        if($this->request->is('get')){
            $this->request->data = $this->request->query;
            if($searchform->execute($this->request->data)){
                #search here
            }
        }


        $this->viewBuilder()->layout('sb_admin');
        $this->set('enabled', true);
        $this->set('enableExecute', ! empty($campaigns));
        $this->set(compact('searchform','reportTitle'));
        $this->set('factorList',$this->factorList);
        $this->set('algoList',$this->algoList);

    }


    public function bulk_update()
    {
        $account_id = $this->request->data('account');
        $data = $this->request->data('data');
        $from = $this->request->data('from');
        $to = $this->request->data('to');

        $from_str = date('Y-m-d', strtotime($from));
        $to_str = date('Y-m-d', strtotime($to));

        $ad_accounts = $this->FacebookAdAccounts->getAdAccountOptions($this->user->id);
        // $this->log($this->request->data, 'debug');

        // Sanity check
        if ($data == null || !array_key_exists($account_id, $ad_accounts)) {
            $this->log('missing data or account id', 'debug');
            return $this->json_response('Invalid request.', 400);
        }
        // $this->log($data, 'debug');
        $campaign_ids = $this->FacebookCampaigns->getCampaignIds($this->user->id, $account_id);

        // $this->log($data, 'debug');

        // Generate bids
        $bids = array();
        foreach ($data as $key => $value) {
            // campaign does not belong to this account
            if (!in_array($value['campaign_id'], $campaign_ids)) {
                $this->log('campaign does not belong to this account', 'debug');
                return $this->json_response('Invalid request.', 400);
            }
            // non-existing factor
            if (!array_key_exists($value['factor'], $this->factorValues)) {
                $this->log('non-existing factor', 'debug');
                return $this->json_response('Invalid request.', 400);
            }

            $multiplier = $this->factorValues[$value['factor']];

            $algo = AlgoBase::create($value['algo']);
            $campaign_bids = $algo->calculate([
                'user_id' => $this->user->id,
                'account_id' => $account_id,
                'campaign_id' => $value['campaign_id'],
                'from' => $from_str,
                'to' => $to_str,
                'cond' => null,
                'multiplier' => $multiplier
            ],[]);

            $bids = array_merge($bids, $campaign_bids);
        }


        // Prepare data for API
        $adset_bids = $this->prepareForApi($bids);
        // $this->log($adset_bids, 'debug');

        $results = array();
        $updated = 0;

        // Cannot send more than 50 batch requests at a time
        $chunks = array_chunk($adset_bids, 50);

        foreach ($chunks as $key => $chunk) {
            try {
                // Send batch to Facebook API
                $responses = $this->fbWrapper->adsets->updateBatch($chunk);

                // Parse responses
                foreach ($responses as $i => $response) {
                    $adset_id = $response['id'];
                    $adset = $chunk[$i]; // response should be in the same order as sent items

                    if ($response['success']) {
                        $updated++;
                    }
                    $results[$adset_id] = array (
                        'status'=> $response['success'],
                        'adset_id'=> $adset_id,
                        'optimization_goal' => $adset['optimization_goal'],
                        'billing_event' => $adset['billing_event'],
                        'bid' => $adset['bid'],
                        //'budget' => $adset['budget'],
                        // 'text' => $msg,
                    );
                }

            } catch (\Exception $e) {
                $errMsg = $e->getMessage();
                $this->log('Error: ' . $errMsg, 'error');
                break; // abort further calls
            }
        }

        // $this->log($responses, 'debug');
        // Save successfully updated adsets to database
        $updated_adsets = [];
        foreach ($results as $result) {
            if ($result['status']) {
                $updated_adsets[] = [
                    'adset_id'=> $result['adset_id'],
                    'optimization_goal' => $result['optimization_goal'],
                    'billing_event' => $result['billing_event'],
                    'bid' => $result['bid'],
                    //'budget' => $result['budget']
                ];
            }
        }
        $this->FacebookAdsets->updateOrInsertMany($updated_adsets, $this->user->id);

        return $this->json_response([
            'requested' => count($adset_bids),
            'updated' => $updated,
            'results' => $results,
        ]);

    }

    // Build the data to send to the facebook API
    private function prepareForApi($bids)
    {
        $updated_adsets = [];
        foreach ($bids as $adset_bid)
        {
            $new_bid = floatval($adset_bid['New Bid']);
            $this->log(var_export($new_bid, true), 'debug');
            if ($new_bid > 0.05)
            {
                // Add to our update list
                $updated_adsets[] = [
                    'adset_id' => $adset_bid['Adset ID'],
                    'bid' => $adset_bid['New Bid'],
                    'billing_event' => $adset_bid['New Billing Event'],
                    'optimization_goal' => $adset_bid['New Optimization Goal'],
                    // budget?
                ];
            }
        }

        return $updated_adsets;
    }


    private function json_response($json, $statusCode=200) {
        $this->autoRender = false;
        $this->response->type('json');
        $this->response->body(json_encode($json));
        $this->response->statusCode($statusCode);
        return $this->response;
    }

}
