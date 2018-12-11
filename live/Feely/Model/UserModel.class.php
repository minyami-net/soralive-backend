<?php
/**
 * Created by PhpStorm.
 * User: tedzy
 * Date: 2017/8/17
 * Time: 16:14
 */

class UserModel extends Model{
    function __construct()
    {
        parent::__construct('user');
    }
    public function addUser($uname, $pass, $roomname, $email){
        $uname=$this->F($uname);
        $pass=$this->F($pass);
        $roomname=$this->F($roomname);
        $email=$this->F($email);

        $this->sql("INSERT INTO `user`(`uname`, `pass`) VALUES (?, ?)", $uname, $pass)->execute();
        $uid = $this->getLastId();
        $this->sql("INSERT INTO `user_detail`(`uid`, `roomname`, `email`) VALUES (?, ?, ?)", $uid, $roomname, $email)->execute();
    }

    public function countUname($uname){
        $uname=$this->F($uname);
        $res = $this->sql("SELECT `uid` FROM `user` WHERE `uname`=?", $uname)->select();
        return count($res);
    }
    public function countEmail($email){
        $email=$this->F($email);
        $res = $this->sql("SELECT `uid` FROM `user_detail` WHERE `email`=?", $email)->select();
        return count($res);
    }
    public function selectFromUname($uname){
        $uname = $this->F($uname);
        $res = $this->sql("SELECT * FROM `user` WHERE `uname`=?", $uname)->select();
        return $res;
    }

    public function selectDetail($uid){
        $uid = $this->F($uid);
        $res = $this->sql("SELECT * FROM `user`, `user_detail` WHERE `user`.`uid`=`user_detail`.`uid` AND `user`.`uid`=?", $uid)->select();
        return $res[0];
    }

    public function updateRN($uid, $roomname, $description){
        $uid = $this->F($uid);
        $roomname = $this->F($roomname);
        $description = $this->F($description);

        return $this->sql("UPDATE `user_detail` SET `roomname`=?, `description`=? WHERE `uid`=?", $roomname, $description, $uid)->execute();
    }

    public function updateSK($uid, $sk, $secretKey){
        $uid = $this->F($uid);
        $sk = $this->F($sk);
        $secretKey = $this->F($secretKey);

        return $this->sql("UPDATE `user` SET `streamkey`=?, `secretkey`=? WHERE `uid`=?", $sk, $secretKey, $uid)->execute();
    }

    public function openRoom($uid){
        $uid = $this->F($uid);
        return $this->sql("UPDATE `user` SET `streaming`=1 WHERE `uid`=?", $uid)->execute();
    }

    public function closeRoom($uid){
        $uid = $this->F($uid);
        return $this->sql("UPDATE `user` SET `streaming`=0 WHERE `uid`=?", $uid)->execute();
    }

    public function listStreaming(){
        $res = $this->sql("SELECT `user`.`uid`, `user`.`uname`, `user_detail`.`roomname` FROM `user` LEFT JOIN `user_detail` ON `user`.`uid`=`user_detail`.`uid` WHERE `user`.`streaming`=1")->select();
        return $res;
    }
}