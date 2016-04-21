<?php
namespace BackEnd\Model;
use BackEnd\Service\UniSession;

/**
 * Class LoginUser
 * @package BackEnd\Model
 * handle any get/set info about logined user
 */
class LoginedUser{
    const NAME = "LoginedUser";
    protected $uniSession;
    public function __construct(){
        $this->uniSession = new UniSession();
    }

    public function set(){
//        if
    }


    public function get(){

    }
}