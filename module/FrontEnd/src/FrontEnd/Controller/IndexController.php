<?php
namespace FrontEnd\Controller;
use App\Config\UniMedia;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\ServiceManager\ServiceManager;
use Zend\Session\Container;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController{
    /** @var  ServiceManager */
    protected $serviceManager;

    public function __construct($serviceManager){
        $this->serviceManager = $serviceManager;
    }

    public function indexAction(){
        $variablesContainer = array();
        $variablesContainer["controller"] = 'FrontEnd\Controller\IndexController\indexAction';
        $sessionContainer = new Container("abc", $this->serviceManager->get("Session"));
//        if($sessionContainer->offsetExists("user")){
//            $variablesContainer['@info'] = "get user from \$sessionContainer";
//            $variablesContainer['user'] = $sessionContainer->offsetGet("user");
//        }else{
//            $variablesContainer['user'] = "you are guest";
//        }
        $viewModel = new ViewModel($variablesContainer);
        return $viewModel;
    }
    public function abcAction(){
        return new ViewModel();
    }
}