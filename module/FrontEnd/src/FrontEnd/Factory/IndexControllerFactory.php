<?php
namespace FrontEnd\Factory;

use FrontEnd\Controller\IndexController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

class IndexControllerFactory implements FactoryInterface{

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator){
        /**
         * Factory many come from Module/Controller/Service
         * if it come from Service -> $serviceLocator is ServiceManager
         * but if it come from Controller/Module -> ControllerManager/ModuleManager
         * by call getServiceLocator, we get back the big daddy ServiceManger
         * @var ServiceManager $serviceManager
         */
        $serviceManager = $serviceLocator->getServiceLocator();
        return new IndexController($serviceManager);
    }
}