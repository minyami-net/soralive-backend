<?php
class UserController extends Controller{
    public function reg(){
        if($_SERVER['REQUEST_METHOD'] == "OPTIONS"){
            $this->returnState(204);
            return;
        }
        $post_raw_data = file_get_contents("php://input");
        $postData = json_decode($post_raw_data, true);

	    if(!isset($postData['uname']) || $postData['uname'] == "") {
            $res['error'] = 1;
            $res['info'] = "tips.usernameNotEmpty";
            $this->returnJson($res, 400);
            return;
        }
	    $uname = $postData['uname'];
        if(!isset($postData['pass']) || $postData['pass'] == "") {
            $res['error'] = 1;
            $res['info'] = "tips.passwordNotEmpty";
            $this->returnJson($res, 400);
            return;
        }
	    $pass = $postData['pass'];
        if(!isset($postData['email']) || $postData['email'] == "") {
            $res['error'] = 1;
            $res['info'] = "tips.emailNotEmpty";
            $this->returnJson($res, 400);
            return;
        }
	    $email = $postData['email'];
        if(!isset($postData['roomname']) || $postData['roomname'] == "") {
            $res['error'] = 1;
            $res['info'] = "tips.roomnameNotEmpty";
            $this->returnJson($res, 400);
            return;
        }
	    $roomname = $postData['roomname'];

	    $userModel = new UserModel();
	    $unameCount = $userModel->countUname($uname);
	    if($unameCount >= 1){
	        $res['error'] = 1;
	        $res['info'] = "tips.usernameUsed";
	        $this->returnJson($res, 401);
	        return;
        }

        $emailCount = $userModel->countEmail($email);
        if($emailCount >= 1){
            $res['error'] = 1;
            $res['info'] = "tips.emailUsed";
            $this->returnJson($res, 401);
            return;
        }

        $hashed_pass = passhash($pass);
        $userModel->addUser($uname, $hashed_pass, $roomname, $email);
        $res['error'] = 0;
        $res['info'] = "info.success";
        $this->returnJson($res, 200);
    }

    public function preLogin(){
	    $token = urlsafe_base64_encode(md5(uniqid(mt_rand(), true), true));

	    //生成rsa密钥
        $rsa_res = openssl_pkey_new();
        $privateKey = '';
        openssl_pkey_export($rsa_res, $privateKey);
        $publicKey = openssl_pkey_get_details($rsa_res)['key'];

        //将对应私钥存入token
        $mem = new Memcached();
        $mem->addServer('localhost', 11211);
        $mem->set($token, $privateKey, time()+60); //需要在60秒内完成登录，否则登录失效

        $res['token'] = $token;
        $res['key'] = $publicKey;
        $this->returnJson($res, 200);
    }
    public function login(){
        if($_SERVER['REQUEST_METHOD'] == "OPTIONS"){
            $this->returnState(204);
            return;
        }
        $post_raw_data = file_get_contents("php://input");
        $postData = json_decode($post_raw_data, true);

        if(!isset($postData['uname']) || $postData['uname'] == "") {
            $res['error'] = 1;
            $res['info'] = "tips.usernameNotEmpty";
            $this->returnJson($res, 400);
            return;
        }
        $uname = $postData['uname'];
        if(!isset($postData['pass']) || $postData['pass'] == "") {
            $res['error'] = 1;
            $res['info'] = "tips.passwordNotEmpty";
            $this->returnJson($res, 400);
            return;
        }
        $pass = $postData['pass'];
        if(!isset($postData['token']) || $postData['token'] == "") {
            $res['error'] = 1;
            $res['info'] = "tips.tokenNotEmpty";
            $this->returnJson($res, 400);
            return;
        }
        $token = $postData['token'];

        $mem = new Memcached();
        $mem->addServer('localhost', 11211);
        if(!$privateKey = $mem->get($token)){
            $res['error'] = 1;
            $res['info'] = "tips.invalidToken";
            $this->returnJson($res, 401);
            return;
        }
        $decryptStatus = openssl_private_decrypt(base64_decode($pass), $realPass, $privateKey, OPENSSL_PKCS1_PADDING);
        $userModel = new UserModel();
        $userInfo = $userModel->selectFromUname($uname);
        if(!$userInfo){
            $res['error'] = 1;
            $res['info'] = "tips.passNotMatched";
            $mem->delete($token);
            $this->returnJson($res, 401);
            return;
        }
        if($userInfo[0]['pass'] != passhash($realPass)){
            $res['error'] = 1;
            $res['info'] = "tips.passNotMatched";
            $mem->delete($token);
            $this->returnJson($res, 401);
            return;
        }

        $mem->delete($token);
        $user = $userInfo[0];
        $newToken = urlsafe_base64_encode(hash_hmac('sha1', uniqid(mt_rand(), true), $user['pass'], true));
        //记录登录信息
        $userSession['uid'] = $user['uid'];
        $userSession['uname'] = $user['uname'];
        $userSession['type'] = $user['type'];
        $userSession['sk'] = urlsafe_base64_encode(md5(uniqid(mt_rand(), true), true));

        $mem->set($newToken, $userSession, time()+86400*30);

        $res['error'] = 0;
        $res['token'] = $newToken;
        $res['user'] = $userSession;
        $this->returnJson($res, 200);
    }
    public function logout(){
        if($_SERVER['REQUEST_METHOD'] == "OPTIONS"){
            $this->returnState(204);
            return;
        }
        $post_raw_data = file_get_contents("php://input");
        $postData = json_decode($post_raw_data, true);

        if(!isset($postData['token']) || $postData['token'] == "") {
            $res['error'] = 1;
            $res['info'] = "tips.tokenNotEmpty";
            $this->returnJson($res, 400);
            return;
        }
        $token = $postData['token'];

        $mem = new Memcached();
        $mem->addServer('localhost', 11211);

        if($memRes = $mem->delete($token)){
            $res['error'] = 0;
            $res['info'] = "info.success";
            $this->returnJson($res, 200);
        }else{
            if(($res['error'] = $mem->getResultCode()) == Memcached::RES_NOTFOUND) {
                $res['info'] = "tips.invalidToken";
                $this->returnJson($res, 401);
            }else{
                $res['info'] = "info.unknownError";
                $this->returnJson($res, 401);
            }
        }


    }

    public function userdetail(){
        if($_SERVER['REQUEST_METHOD'] == "OPTIONS"){
            $this->returnState(204);
            return;
        }
        $post_raw_data = file_get_contents("php://input");
        $postData = json_decode($post_raw_data, true);

        if(!isset($postData['token']) || $postData['token'] == "") {
            $res['error'] = 1;
            $res['info'] = "tips.tokenNotEmpty";
            $this->returnJson($res, 400);
            return;
        }
        $token = $postData['token'];

        $mem = new Memcached();
        $mem->addServer('localhost', 11211);

        if($memRes = $mem->get($token)){
            $uid = $memRes['uid'];
            $userModel = new UserModel();
            $userRes = $userModel->selectDetail($uid);

            //计算串流码
            if($userRes['streamkey']!=null){
                $sk = md5($userRes['uid'].$userRes['streamkey'].$userRes['secretkey']."UpK19z%y".$userRes['pass']);
                $userRes['streamkey'] = $userRes['streamkey']."?streamingid=".$userRes['uid'].':'.$sk;
            }

            $userRes['pass']='';
            $userRes['secretkey']='';
            $res['error'] = 0;
            $res['user'] = $userRes;
            $res['streaming_address'] = Core::config("streaming_address");
            $this->returnJson($res,200);
        }else{
            if(($res['error'] = $mem->getResultCode()) == Memcached::RES_NOTFOUND) {
                $res['info'] = "tips.invalidToken";
                $this->returnJson($res,401);
            }else{
                $res['info'] = "info.unknownError";
                $this->returnJson($res,401);
            }
        }

    }
    public function updateRn(){
        if($_SERVER['REQUEST_METHOD'] == "OPTIONS"){
            $this->returnState(204);
            return;
        }
        $post_raw_data = file_get_contents("php://input");
        $postData = json_decode($post_raw_data, true);

        if(!isset($postData['token']) || $postData['token'] == "") {
            $res['error'] = 1;
            $res['info'] = "tips.tokenNotEmpty";
            $this->returnJson($res, 400);
            return;
        }
        $token = $postData['token'];
        $roomname="";
        if(isset($postData['roomname'])){
            $roomname=$postData['roomname'];
        }
        $description="";
        if(isset($postData['description'])){
            $description=$postData['description'];
        }

        $mem = new Memcached();
        $mem->addServer('localhost', 11211);

        if($memRes = $mem->get($token)){
            $uid = $memRes['uid'];
            $userModel = new UserModel();
            $userRes = $userModel->updateRN($uid, $roomname, $description);
            if($userRes){
                $res['error'] = 0;
                $res['info'] = "info.success";
                $this->returnJson($res, 200);
            }else{
                $res['error'] = 70;
                $res['info'] = "info.databaseError";
                $this->returnJson($res, 500);
            }
        }else{
            if(($res['error'] = $mem->getResultCode()) == Memcached::RES_NOTFOUND) {
                $res['info'] = "tips.invalidToken";
                $this->returnJson($res,401);
            }else{
                $res['info'] = "info.unknownError";
                $this->returnJson($res,401);
            }
        }
    }

    public function reset_upkey(){
        if($_SERVER['REQUEST_METHOD'] == "OPTIONS"){
            $this->returnState(204);
            return;
        }
        $post_raw_data = file_get_contents("php://input");
        $postData = json_decode($post_raw_data, true);

        if(!isset($postData['token']) || $postData['token'] == "") {
            $res['error'] = 1;
            $res['info'] = "tips.tokenNotEmpty";
            $this->returnJson($res, 400);
            return;
        }
        $token = $postData['token'];

        $mem = new Memcached();
        $mem->addServer('localhost', 11211);

        if($memRes = $mem->get($token)){
            $uid = $memRes['uid'];
            $userModel = new UserModel();

            $dict = "0123456789abcdef";
            $raw_sk="sl_".$uid;
            for($i = 0; $i < 6; $i++){
                $raw_sk .= substr($dict, mt_rand()%strlen($dict), 1);
            }
            $secretKey = urlsafe_base64_encode(md5(uniqid(mt_rand(), true), true));



            $userRes = $userModel->updateSK($uid, $raw_sk, $secretKey);
            if($userRes){
                $res['error'] = 0;
                $res['info'] = "info.success";
                $this->returnJson($res, 200);
            }else{
                $res['error'] = 70;
                $res['info'] = "info.databaseError";
                $this->returnJson($res, 500);
            }
        }else{
            if(($res['error'] = $mem->getResultCode()) == Memcached::RES_NOTFOUND) {
                $res['info'] = "tips.invalidToken";
                $this->returnJson($res,401);
            }else{
                $res['info'] = "info.unknownError";
                $this->returnJson($res,401);
            }
        }
    }

    public function changeRoomStatus(){
        if($_SERVER['REQUEST_METHOD'] == "OPTIONS"){
            $this->returnState(204);
            return;
        }
        $post_raw_data = file_get_contents("php://input");
        $postData = json_decode($post_raw_data, true);

        if(!isset($postData['token']) || $postData['token'] == "") {
            $res['error'] = 1;
            $res['info'] = "tips.tokenNotEmpty";
            $this->returnJson($res, 400);
            return;
        }
        if(!isset($postData['status'])) {
            $res['error'] = 1;
            $res['info'] = "tips.setStatusNotEmpty";
            $this->returnJson($res, 400);
            return;
        }
        $token = $postData['token'];

        $mem = new Memcached();
        $mem->addServer('localhost', 11211);

        if($memRes = $mem->get($token)){
            $uid = $memRes['uid'];
            $userModel = new UserModel();

            if($postData['status'] == 1){
                $userModel->openRoom($uid);
                $res['error'] = 0;
                $res['info'] = "info.success";
                $this->returnJson($res, 200);
            }elseif($postData['status'] == 0){
                $userModel->closeRoom($uid);
                $res['error'] = 0;
                $res['info'] = "info.success";
                $this->returnJson($res, 200);
            }else{
                $res['error'] = 1;
                $res['info'] = "tips.setStatusError";
                $this->returnJson($res, 400);
                return;
            }
        }else{
            if(($res['error'] = $mem->getResultCode()) == Memcached::RES_NOTFOUND) {
                $res['info'] = "tips.invalidToken";
                $this->returnJson($res,401);
            }else{
                $res['info'] = "info.unknownError";
                $this->returnJson($res,401);
            }
        }
    }
}