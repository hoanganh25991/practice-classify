<?php
namespace BackEnd;

use App\Config\UniMedia;
use BackEnd\Controller\AuthController;
use BackEnd\Service\UniAcl;
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
        /*
         * if new update come to $cache >>> read from $cache to check ACL
         */
        /*
         * logged in user, has checked where to go
         * save it in session
         * read from session
         */
        $routeMatch = $mvcEvent->getRouteMatch();
        $thisController = $routeMatch->getParam("controller");
        $thisAction = $routeMatch->getParam("action");
        $uniSession = new UniSession();
        $user = $uniSession->get(UniSession::LOGGED_IN_USER, AuthController::USER);
        /*
         * viec check user do ACL???
         * ACL chi check dua tren $role, $resource, $privilege
         * $role special
         * user special
         * >>> phai check qua >>>
         * <<< phai lam sao >>>>
         */
        if($user){
            /*
             * user logged in
             */
            if(isset($user["isUniAllowed"])){
                /*
                 * user has checked ACL
                 */
                /*
                 * check user has this $controller
                 * how to save to UniACL, loop qua cho de dang
                 */
                if(isset($user["isUniAllowed"][$thisController])){
                   foreach($user["isUniAllowed"][$thisController] as $action){
                       if($thisAction === $action){
                           //OK
                           //allow go ahead
                           //HOW TO GO AHEAD
                           return;
                       }
                   }
                }

            }
        }

        /*
         * handle fallback
         */

    }
}