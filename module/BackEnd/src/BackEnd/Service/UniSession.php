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
    const USER = "USER";
    const USER_LOGGED = "LOGGED";
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

    /**
     * @param string $className
     * @param string $event
     * @return array
     */
    public function get($className, $event){
        $container = new Container($className, $this->manager);
        if($container->offsetExists($event)){
            return $container->offsetGet($event);
        }
        return array();
    }

    public function remove($className, $event){
        $container = new Container($className, $this->manager);
        if($container->offsetGet($event)){
            $container->offsetUnset($event);
        };
    }
}