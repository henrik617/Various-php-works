<?php


namespace App\Controller;


use Cake\Controller\Controller;
//use App\Controller\AppController;
use App\BL\Customization;
use Cake\Core\Configure;


class ExternalController extends Controller
{
    public function index()
    {
        
    }
    public function add(){
        $this->loadModel('FacebookInsights');
        $ret = array("result" => "Success");
        $result = false;
        if (isset($this->request->query["date"]) && isset($this->request->query["facebook_ad_id"]) && 
            isset($this->request->query["facebook_account_id"]) &&
            isset($this->request->query["ftd"]) && isset($this->request->query["register"]) && 
            isset($this->request->query["user_id"]))
        {
            $row = array();
            $row["dates"] = $this->request->query["date"];
            $row["adgroup_id"] = $this->request->query["facebook_ad_id"];
            $row["account_id"] = $this->request->query["facebook_account_id"];
            $row["ftd"] = $this->request->query["ftd"];
            $row["registrations"] = $this->request->query["register"];
            $row["user_id"] = $this->request->query["user_id"];
            $result = $this->FacebookInsights->addExternalContent($row);
        }
        if ($result == true)
            echo json_encode($ret);
        else
            echo json_encode(array("result" => "Failed to save"));
        exit;
    }
    public function bulk_update(){

        $this->loadModel('FacebookInsights');
        $this->loadModel('ExternalTrans');
        $ret_success = array("result" => "Success");
        $ret_fail = array("result" => "Failed to update");
        $result = false;

        // $arr_example = array();
        // for ($i=1;$i<3;$i++)
        // {
        //     $each = array();
        //     $each["date"] = "2016-07-1$i";
        //     $each["facebook_ad_id"] = "341243213243$i";
        //     $each["facebook_account_id"] = "account_$i";
        //     $each["ftd"] = $i+1;
        //     $each["register"] = ($i+1)*2;
        //     $each["user_id"] = "20-16-7-12-0$i";
        //     $arr_example[] = $each;
        // }
        // for ($i=2;$i<5;$i++)
        // {
        //     $each = array();
        //     $each["date"] = "2016-07-1$i";
        //     $each["facebook_ad_id"] = "341243213243$i";
        //     $each["facebook_account_id"] = "account_$i";
        //     $each["ftd"] = $i+1;
        //     $each["register"] = ($i+1)*2;
        //     $each["user_id"] = "20-16-7-12-0$i";
        //     $arr_example[] = $each;
        // }
        // echo json_encode($arr_example);
        if ($this->request->is('post'))
        {
            $json_data = $this->request->data["data"];
            $data = json_decode($json_data);
            $length = sizeof($data);
            $today = date("Y-m-d");
            $include_today = false; $initial_accept = false;

            for ($i=0;$i<$length;$i++)
            {
                if ($i !=0 && $data[$i]->date != $data[0]->date)
                {
                    $initial_accept = true;
                }
                if ($data[$i]->date == $today){
                    $include_today = true; break;
                }
            }
            if ($initial_accept == true && $include_today == true){
                $result = $this->FacebookInsights->bulkUpdateExternalContent($data);
                $result = $result && $this->ExternalTrans->bulkUpdateExternalContent($data);
            }
            else{
                if ($initial_accept == false)
                    $ret_fail = array("result" => "There should be minimum of 2 days data");
                if ($include_today == false)
                    $ret_fail = array("result" => "Need to contain today's data");
            }
        }
        if ($result == true)
            echo json_encode($ret_success);
        else
            echo json_encode($ret_fail);
        exit;
    }
}
