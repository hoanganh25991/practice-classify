<?php
namespace BackEnd\Service;

use Zend\Session\Container;
use Zend\Session\SessionManager;
use Zend\Session\Storage\SessionArrayStorage;

/**
 * UniSession handle storage into global $_SESSION
 *
 */
class UniSession{
    protected $manager;

    public function __construct(){
        $manager = new SessionManager();
        $storage = new SessionArrayStorage();
        $manager->setStorage($storage);
        $this->manager = $manager;
    }

    public function set($className, $event, $value){
        $container = new Container($className, $this->manager);
        $container->offsetSet($event, $value);
    }

    public function get($className, $event){
        $container = new Container($className, $this->manager);
        return $container->offsetGet($event);
    }
}