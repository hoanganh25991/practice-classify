<?php
namespace BackEnd;

use App\Config\UniMedia;
use BackEnd\Service\UniAcl;
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
//        $app = $mvcEvent->getApplication();
//        //        $eventManager = $app->getEventManager();
//        /** @var ServiceManager $serviceManger */
//        $serviceManger = $app->getServiceManager();
//        //        $uniAcl = new UniAcl($serviceManger);
//        /** @var UniAcl $uniAcl */
//        $uniAcl = $serviceManger->get('UniAcl');
//        $uniAcl->init();
//        $sessionManager = $serviceManger->get('Session');
//        $sessionContainer =
//            new Container(UniMedia::SESSION_CONTAINER, $sessionManager);
//        $userId = null;
//        if($sessionContainer->offsetExists("user")){
//            $user = $sessionContainer->offsetGet("user");
//            $userId = $user["id"];
//        }
//        $thisController = $mvcEvent->getRouteMatch()->getParam('controller');
//        if(!$uniAcl->isUniAllowed($userId, $thisController)){
//            die('Permission denied');
//        }
    }
}