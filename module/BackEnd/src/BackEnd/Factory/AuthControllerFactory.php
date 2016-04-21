<?php
namespace BackEnd\Factory;
use BackEnd\Controller\AuthController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

class AuthControllerFactory implements FactoryInterface{

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator){
        /**
         * Factory may come from Module/Controller/Service
         * if it come from Service -> $serviceLocator is ServiceManager
         * but if it come from Controller/Module -> ControllerManager/ModuleManager
         * by call getServiceLocator, we get back the big daddy ServiceManger
         * @var ServiceManager $serviceManager */
        $serviceManager = $serviceLocator->getServiceLocator();
        return new AuthController($serviceManager);
    }
}