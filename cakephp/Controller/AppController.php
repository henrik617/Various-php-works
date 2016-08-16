<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link      http://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link http://book.cakephp.org/3.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{

    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * @return void
     */

    public function initialize()
    {
        $this->loadComponent('Flash');
        $this->loadComponent('Auth', [
            'loginRedirect' => [
                'controller' => 'Reports',
                'action' => 'index'
            ],
            'logoutRedirect' =>'/'

//                [
//                'controller' => 'Users',
//                'action' => 'login'
//            ]
        ]);
    }

    public function beforeFilter(Event $event)
    {
        date_default_timezone_set('Etc/GMT-2');
//        $this->Auth->allow(['index', 'view', 'display']);
        $this->log ("New Request Action name: ". $this->request->action);
        $this->isLoggedIn = false;
        $this->user=null;

        if ($this->Auth->user())
        {
            $this->isLoggedIn = true;
            $users =TableRegistry::get('Users');
            $this->user=$users->get($this->Auth->user('id'));

            if ($this->user['role']=='client' &&  ($this->request->action!='dashboard' && $this->request->action!='logout'))
            {
                $this->log ('Client user -> limiting action - back to dashboard');
                $this->redirect('/reports/dashboard');
            }

            $this->getNotifications();

        }
        else
        {
            $this->log ('user is not connected, redireciting to login');
            // && $this->request->action!='add'
            if ($this->request->action!='login')
            {
                $this->redirect('/users/login');
            }

        }

        $this->log ("APP Before Filter End: ". $this->request->action);
    }



    public function getNotifications ()
    {
        $this->loadModel('Notifications');
        $Notifications =TableRegistry::get('Notifications');
//        $notifications = $Notifications
//            ->find ('all')
//            ->where (['k'=>'insights_update'])
//            ->order(['id DESC'])
//            ->limit(1)
//            ->toArray();

        $notifications = $Notifications
            ->find ('all')->where(['user_id' => $this->user->id ]);

        $notifications=$notifications->select (['id' => $notifications->func()->max('id'),'created' => $notifications->func()->max('created'),'k','value'])
            ->order(['id DESC'])
            ->group (['k'])
            ->toArray();



//        debug ($notifications);exit;
        $this->set ('notifications',$notifications);

    }
}
?>
