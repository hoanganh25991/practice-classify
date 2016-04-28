<?php
namespace FrontEnd\Controller;
use App\Config\UniMedia;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Permissions\Acl\Acl;
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
//        $a = new Acl();
//        $a->addRole("a");
//        $a->addResource("ra");
//        $a->allow("a", "ra");
//        $a->allow("a", "ra");
//        $a->allow("a", "ra");
//        $a->allow("a", "ra");
//        $a->deny("a", "ra");
//        var_dump($a->isAllowed("a", "ra"));
        $variablesContainer = array();
        $variablesContainer["controller"] = 'FrontEnd\Controller\IndexController\indexAction';
//        $sessionContainer = new Container("abc", $this->serviceManager->get("Session"));
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