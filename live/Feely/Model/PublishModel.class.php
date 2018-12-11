<?php
/**
 * Created by PhpStorm.
 * User: tedzy
 * Date: 2017/8/25
 * Time: 13:50
 */

class PublishModel extends Model{
    function __construct(){
        parent::__construct("publish_log");
    }
    public function add($addr, $uid, $info, $status){
        $addr = $this->F($addr);
        $uid = $this->F($uid);
        $info = $this->F($info);
        $status = $this->F($status);

        return $this->sql("INSERT INTO `publish_log`(`addr`, `uid`, `info`, `status`) VALUES (?, ?, ?, ?)", $addr, $uid, $info, $status)->execute();
    }
}