<?php
/**
 * Created by PhpStorm.
 * User: tedzy
 * Date: 2017/8/25
 * Time: 13:26
 */

class ServerController extends Controller{
    public function on_publish(){
        $uid = 0;

        $info['call'] = $_GET['call'];
        $info['addr'] = $_GET['addr'];
        $info['clientid'] = $_GET['clientid'];
        $info['app'] = $_GET['app'];
        $info['flashVer'] = $_GET['flashVer'];
        $info['swfUrl'] = $_GET['swfUrl'];
        $info['tcUrl'] = $_GET['tcUrl'];
        $info['pageUrl'] = $_GET['pageUrl'];
        $info['name'] = $_GET['name'];

        $publishModel = new PublishModel();

        if(!isset($_GET['streamingid'])){
            $publishModel->add($info['addr'], $uid, json_encode($info, JSON_UNESCAPED_UNICODE), "NO_SK");
            $this->returnState(401);
            return;
        }

        $streamid = $_GET['streamingid'];
        $streamStatusArr = explode(":", $streamid);
        if(count($streamStatusArr) != 2){
            $publishModel->add($info['addr'], $uid, json_encode($info, JSON_UNESCAPED_UNICODE), "INVALID_SK");
            $this->returnState(401);
            return;
        }

        $uid = $streamStatusArr[0];
        $signed_key = $streamStatusArr[1];

        $userModel = new UserModel();
        $userRes = $userModel->selectDetail($uid);

        if(!$userRes){
            $publishModel->add($info['addr'], $uid, json_encode($info, JSON_UNESCAPED_UNICODE), "INVALID_UID");
            $this->returnState(401);
            return;
        }

        $sk = md5($userRes['uid'].$userRes['streamkey'].$userRes['secretkey']."UpK19z%y".$userRes['pass']);
        if($signed_key != $sk){
            $publishModel->add($info['addr'], $uid, json_encode($info, JSON_UNESCAPED_UNICODE), "WRONG_SK");
            $this->returnState(401);
            return;
        }

        if($userRes['streaming'] == 0){
            $publishModel->add($info['addr'], $uid, json_encode($info, JSON_UNESCAPED_UNICODE), "ROOM_OFF");
            $this->returnState(401);
            return;
        }


        $publishModel->add($info['addr'], $uid, json_encode($info, JSON_UNESCAPED_UNICODE), "SUCCESS");

        $this->returnState(204);

    }
}