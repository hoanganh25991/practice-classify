<?php
namespace BackEnd\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Session\SessionManager;
use Zend\Session\Storage\SessionArrayStorage;

class SessionManagerFactory implements FactoryInterface{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator){
        $sessionManger = new SessionManager();
        $sessionStorage = new SessionArrayStorage();
        $sessionManger->setStorage($sessionStorage);
        return $sessionManger;
    }
}