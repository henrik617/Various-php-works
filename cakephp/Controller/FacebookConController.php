<?php
/**
 * Created by PhpStorm.
 * User: syaron66
 * Date: 12/4/2015
 * Time: 7:37 PM
 */

namespace App\Controller;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\I18n\Time;
use Cake\Network\Exception\NotFoundException;
use Cake\View\Exception\MissingTemplateException;
use Cake\ORM\TableRegistry;
use Cake\Event\Event;
use Cake\Routing\Router;


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

class FacebookConController extends AppController {
    public function beforeFilter (Event $event)
    {

        $this->log("Facebook Before Filter start: " . $this->request->action);

        parent::beforeFilter($event);

    }

//facebookcon/connect

    public function connect ()
    {

        $fb = new Facebook([
            'app_id' => '1498322540487595',
            'app_secret' => '77367f1002752fc852cc476e6c1d6b0f',
        ]);


        //removing old facebook token from cookie
        $_SESSION['facebook_access_token'] = null;

        $helper = $fb->getRedirectLoginHelper();

        if (!isset($_SESSION['facebook_access_token'])) {
            $_SESSION['facebook_access_token'] = null;
        }

        if (!$_SESSION['facebook_access_token']) {
            $helper = $fb->getRedirectLoginHelper();
            try {
                $_SESSION['facebook_access_token'] = (string) $helper->getAccessToken();
            } catch(FacebookResponseException $e) {
                // When Graph returns an error
                echo 'Graph returned an error: ' . $e->getMessage();
                exit;
            } catch(FacebookSDKException $e) {
                // When validation fails or other local issues
                echo 'Facebook SDK returned an error: ' . $e->getMessage();
                exit;
            }
        }

        if ($_SESSION['facebook_access_token']) {
            echo "You are logged in!";
        } else {
            $permissions = ['ads_management', 'manage_pages', 'publish_pages'];
            $url = Router::url(['controller' => 'FacebookCon', 'action' => 'connected'], true);
            // $url="http://facebook.pareto.sl/facebook_con/connected";
            $loginUrl = $helper->getLoginUrl($url, $permissions);
            echo '<a href="' . $loginUrl . '">Log in with Facebook</a>';
        }
        exit;
    }


    public function connected ()
    {
        echo "connected";


        $fb = new Facebook([
            'app_id' => '1498322540487595',
            'app_secret' => '77367f1002752fc852cc476e6c1d6b0f',
        ]);



        if (!$_SESSION['facebook_access_token']) {
            $helper = $fb->getRedirectLoginHelper();
            try {
                $_SESSION['facebook_access_token'] = (string) $helper->getAccessToken();
            } catch(FacebookResponseException $e) {
                // When Graph returns an error
                echo 'Graph returned an error: ' . $e->getMessage();
                exit;
            } catch(FacebookSDKException $e) {
                // When validation fails or other local issues
                echo 'Facebook SDK returned an error: ' . $e->getMessage();
                exit;
            }
        }

        var_dump ("short token",$_SESSION['facebook_access_token']);

        //replace with long live token
        $accessToken =$_SESSION['facebook_access_token'];
        $oAuth2Client = $fb->getOAuth2Client();
        $longLivedAccessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
        $_SESSION['facebook_access_token']= $longLivedAccessToken->getValue();
        $accessToken =$longLivedAccessToken->getValue();
        var_dump ("long token",$_SESSION['facebook_access_token']);

        //save or update long token into DB
        $response = $fb->get('/me?locale=en_US&fields=name,email',$accessToken);
        $userNode = $response->getGraphUser();
        $userEmail = $userNode->getField('email');

        //check if email exist, update or create new
        $facebookTokens =TableRegistry::get('FacebookTokens');
        $tokenQuery = $facebookTokens->find()->where(['email' => $userEmail]);
        $tokenData  = $tokenQuery->first();

        //var_dump($tokenData);




        if (!$tokenData)
        {
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
            $now = new Time (date ('Y-m-d h:i:s',microtime(true)));
            $tokenData->access_token=$accessToken;
            $tokenData->modified = $now;
            echo "updating new toekn";
            $res = $facebookTokens->save($tokenData);
            debug ($res);
            exit;


            debug ($token_id);



        //    $token = $facebookTokens>get ($token_id);


            $new_data = ['id'=>$token_id,'access_token'=>$accessToken,'modified'=>$now];
//            $token->access_token = $accessToken;

            $res = $facebookTokens->save($new_data);
            echo "res<br>";
            var_dump($res);
            echo "the token was updated in DB";
        }
        exit;


    }

}