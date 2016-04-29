<?php
namespace BackEnd;

use BackEnd\Database\AclTable;
use BackEnd\Service\UniAcl;
use BackEnd\Service\UniCache;
use BackEnd\Service\UniSession;
use Zend\Mvc\Application;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceManager;
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
        /**
         * if user try to log in, log out, join
         * means any action from 'BackEnd\Controller\Auth'
         * NOT applied check acl
         */
        $routeMatchInfo = $mvcEvent->getRouteMatch()->getParams();
        if($routeMatchInfo["controller"] === 'BackEnd\Controller\Auth'){
            return;
        }

        $serviceManager = $mvcEvent->getApplication()->getServiceManager();
        /**
         * get uni acl config
         */
        /** @var UniCache $cache */
        $cache = $serviceManager->get("UniCache");

        /** @var array $uniAclConfig */
        $uniAclConfig = $cache->getArrayItem(UniAcl::CONFIG);
        if(!(count($uniAclConfig) > 0)){
            $uniAclConfig = array();

            /**
             * read controller-action from application-config
             */
            /** @var Application $app */
            $config = $serviceManager->get("config");

            $controllerAction = array();
            $routes = $config["router"]["routes"];
            foreach($routes as $route){
                $defaults = $route["options"]["defaults"];
                $controllerAction[$defaults["controller"]][] = $defaults["action"];
            }

            $uniAclConfig[UniAcl::CONTROLLER_ACTION] = $controllerAction;
            /**
             * default config set up in module.config.php
             * follow rules of UniAcl
             */
            $defaultConfig = $config[UniAcl::CONFIG];
            /**
             * add controller-action for default
             * no one knows "where", only application-config has it
             */
            $defaultConfig[UniAcl::CONTROLLER_ACTION] = $uniAclConfig[UniAcl::CONTROLLER_ACTION];
            /**
             * save to cache
             */
            $uniAclConfig = $defaultConfig;
            $cache->setArrayItem(UniAcl::CONFIG, $defaultConfig);
            /** @var AclTable $aclTable */
            /**
             * save to db
             */
            $aclTable = $serviceManager->get('AclTable');
            $aclTable->insert($defaultConfig);
        }
        /**
         * init uni acl by config
         */
        $uniAcl = new UniAcl($uniAclConfig);
        $uniAcl->init();
        /**
         * get user from session
         */
        $uniSession = new UniSession();
        /**
         * if user not follow UniAcl default
         * (has $user["role"], $user["id"]
         * role default is "guest"
         * id default is ""
         */
        $user = $uniSession->get(UniSession::USER, UniSession::USER_LOGGED);

        $isAllowed = $uniAcl->isUniAllowed($user, $routeMatchInfo["controller"], $routeMatchInfo["action"]);
        if(!$isAllowed){
            die("permission deny");
        }
    }
}
