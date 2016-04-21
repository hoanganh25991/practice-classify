<?php
namespace BackEnd\Factory;

use BackEnd\Service\UniCache;
use Zend\Cache\StorageFactory;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class UniCacheFactory implements FactoryInterface{
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
        $plugin = StorageFactory::pluginFactory('exception_handler', array(
            'throw_exceptions' => false,
        ));
        $uniCache = new UniCache();
        $uniCache->addPlugin($plugin);
        $uniCache->setOptions(array('cache_dir' => './data/cache'));
        return $uniCache;
    }
}