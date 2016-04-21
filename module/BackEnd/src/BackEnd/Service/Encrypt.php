<?php
namespace BackEnd\Service;
class Encrypt{
  const KEY = "unimedia.vn";
  public static function hash($password){
    return sha1(Encrypt::KEY.$password);
  }
}