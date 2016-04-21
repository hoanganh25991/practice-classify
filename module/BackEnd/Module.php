<?php
namespace BackEnd;

use App\Config\UniMedia;
use BackEnd\Controller\AuthController;
use BackEnd\Service\UniAcl;
use BackEnd\Service\UniCache;
use BackEnd\Service\UniSession;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceManager;
use Zend\Session\Container;
use Zend\Stdlib\ArrayUtils;

class Module{
    public function getConfig(){
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig(){
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function onBootstrap(MvcEvent $mvcEvent){
        $app = $mvcEvent->getApplication();
        $eventManager = $app->getEventManager();
        /** @var ServiceManager $serviceManger */
        //        $serviceManger = $app->getServiceManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        $eventManager->attach(MvcEvent::EVENT_DISPATCH, array(
            $this,
            'checkAuthAcl'
        ), 1);

    }

    public function checkAuthAcl(MvcEvent $mvcEvent){
        /*
         * read access controll config from  cache
         */
        /** @var UniCache $cache */
        $cache = $mvcEvent->getApplication()->getServiceManager()->get("UniCache");

        /** @var array|false $aclConfig */
        $aclConfig = $cache->getArrayItem(UniAcl::CONFIG);
        if(!$aclConfig){
            $aclConfig = array();

            $config = $mvcEvent->getApplication()->getServiceManager()->get("config");
            $invokablesController = $config['controllers']['invokables'];
            $factoriesController = $config['controllers']['factories'];
            /** @var array $aclResources */
            $controllerArray = array_merge(array_keys($invokablesController), array_keys($factoriesController));

            $aclConfig[UniAcl::CONTROLLER] = $controllerArray;
        }

        $uniAcl = new UniAcl($aclConfig);


    }
}