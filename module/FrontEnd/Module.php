<?php
namespace FrontEnd;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Navigation\Page\Mvc;

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

    public function onBootstrap(MvcEvent $e){
        $eventManager = $e->getApplication()->getEventManager();
//        $serviceManager = $e->getApplication()->getServiceManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        $eventManager->attach(MvcEvent::EVENT_DISPATCH, array(
            $this,
            'setLayout'
        ), 100);
    }

    /**
     * method need PUBLIC, because it called by EventManager (some one out side this scope)
     * @param  \Zend\Mvc\MvcEvent $mvcEvent The MvcEvent instance
     * @return void
     */
    public function setLayout(MvcEvent $mvcEvent){
        $routeMatch = $mvcEvent->getRouteMatch();
        $controller = $routeMatch->getParams()["controller"];
        /**
         * @warn uncomplete
         * set layout
         */
        $viewModel = $mvcEvent->getViewModel();
        $viewModel->setTemplate('FrontEnd\Layout');
    }
}