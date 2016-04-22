<?php
namespace BackEnd;

use App\Config\UniMedia;
use BackEnd\Controller\AuthController;
use BackEnd\Service\UniAcl;
use BackEnd\Service\UniCache;
use BackEnd\Service\UniSession;
use Zend\Mvc\Application;
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
            'checkAcl'
        ), 100);
    }

    public function checkAcl(MvcEvent $mvcEvent){
        //if user try to log in, log out, join
        //do not apply check acl
        $routeMatchInfo = $mvcEvent->getRouteMatch()->getParams();
        if($routeMatchInfo["controller"] === 'BackEnd\Controller\Auth'){
            return;
        }

        $serviceManager = $mvcEvent->getApplication()->getServiceManager();
        /**
         * GET ACL CONFIG
         */
        /** @var UniCache $cache */
        $cache = $serviceManager->get("UniCache");

        /** @var array $aclConfig */
        $aclConfig = $cache->getArrayItem(UniAcl::CONFIG);
        if(!(count($aclConfig) > 0)){
            $aclConfig = array();

            /** @var Application $app */
            $config = $serviceManager->get("config");
            //            $invokablesController = $config['controllers']['invokables'];
            //            $factoriesController = $config['controllers']['factories'];
            //            $controllerArray = array_merge(array_keys($invokablesController), array_keys($factoriesController));

            /*
             * 'router' => array(
                    'routes' => array(
                        'backend' => array(
                            'type' => 'literal',
                            'options' => array(
                                'route' => '/backend',
                                'defaults' => array(
                                    'controller' => 'BackEnd\Controller\Test',
                                    'action' => 'index',
                                ),
                            ),
                        ),
                    ),
                )
             */
            $controllerAction = array();
            $routes = $config["router"]["routes"];
            foreach($routes as $route){
                $defaults = $route["options"]["defaults"];
                $controllerAction[$defaults["controller"]][] = $defaults["action"];
            }


            $aclConfig[UniAcl::CONTROLLER_ACTION] = $controllerAction;
            $cache->setArrayItem(UniAcl::CONFIG, $aclConfig);
        }

        /**
         * INIT ACL BY CONFIG
         */
        $uniAcl = new UniAcl($aclConfig);
        $uniAcl->init();

        /**
         * GET USER FROM SESSION
         */
        $uniSession = new UniSession();
        $user = $uniSession->get(UniSession::USER, UniSession::USER_LOGGED);

        /*
         *
         */
        $isAllowed = $uniAcl->isUniAllowed($user, $routeMatchInfo["controller"], $routeMatchInfo["action"]);
        if(!$isAllowed){
            die("permission deny");
        }
    }
}