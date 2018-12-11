<?php
class IndexController extends Controller{
	public function index($page=1){
		$assign['title'] = ($page==1?"":("第".$page."页 - "))."首页";
		$this->display($assign, 'index');	
	}
	public function info(){
	    phpinfo();
	}
	public function getlist(){
		$userModel = new UserModel();
		$list = $userModel->listStreaming();

		$this->returnJson($list, 200);
	}
	public function playurl(){
		if($_SERVER['REQUEST_METHOD'] == "OPTIONS"){
            $this->returnState(204);
            return;
        }
        $post_raw_data = file_get_contents("php://input");
        $postData = json_decode($post_raw_data, true);

        if(!isset($postData['uid']) || $postData['uid'] == "") {
            $res['error'] = 1;
            $res['info'] = "tips.uidInvalid";
            $this->returnJson($res, 400);
            return;
		}
		
		$uid = $postData['uid'];
		$userModel = new UserModel();
		$resUser = $userModel->selectDetail($uid);

		if(!$resUser){
			$res['error'] = 1;
            $res['info'] = "tips.userInvalid";
            $this->returnJson($res, 400);
            return;
		}

		if($resUser['type'] != 1){
			$res['error'] = 1;
            $res['info'] = "tips.userInvalid";
            $this->returnJson($res, 400);
            return;
		}

		$res['error'] = 0;
		$res['uname'] = $resUser['uname'];
		$res['streaming_uri'] = Core::config("livestreaming_prefix").$resUser['streamkey'].'.m3u8';
		$res['streaming'] = $resUser['streaming'];
		$res['roomname'] = $resUser['roomname'];
		$res['description'] = $resUser['description'];
		$this->returnJson($res, 200);
	}

}