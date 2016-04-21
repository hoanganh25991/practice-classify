<?php
namespace BackEnd\Controller;

use App\Config\UniMedia;
use BackEnd\DbQuery\UserTableQuery;
use BackEnd\Form\LoginFilter;
use BackEnd\Form\LoginForm;
use BackEnd\Service\Encrypt;
use Zend\Http\Request;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\Container;
use Zend\Session\SessionManager;
use Zend\Session\Storage\SessionArrayStorage;
use Zend\Stdlib\ParametersInterface;
use Zend\View\Model\ViewModel;

class AuthController extends AbstractActionController{
    protected $serviceManager;
    protected $loginForm;

    public function __construct($serviceManager){
        $this->serviceManager = $serviceManager;
    }

    public function loginAction(){
        $serviceManager = $this->serviceManager;
        $this->loginForm = new LoginForm("loginForm");
        //add filer to loginForm
        $loginFilter = new LoginFilter();
        $this->loginForm->setInputFilter($loginFilter);
        //if method is POST, handle auth
        /** @var Request $request */
        $request = $this->getRequest();
        if($request->isPost()){
            /** @var ParametersInterface $data */
            $data = $request->getPost();
            $this->loginForm->setData($data);
            if($this->loginForm->isValid()){
                //handle on loginForm valid
                //call SimpleAuth without register it into ServiceManger
                $email = $data->get('email');
                /** @var UserTableQuery $userTableQuery */
                $userTableQuery = $serviceManager->get("UserTableQuery");
                $user = $userTableQuery->findWhere(array('email' => $email));
                $encryptPass = Encrypt::hash($data->get('password'));
                if($encryptPass !== $user['password']){
                    $user = false;
                }
                if($user){
                    $sessionManager = new SessionManager();
                    $sessionStorage = new SessionArrayStorage();
                    $sessionManager->setStorage($sessionStorage);
                    $sessionContainer =
                        new Container(UniMedia::SESSION_CONTAINER,
                            $sessionManager);
                    $sessionContainer->offsetSet("user", $user);
                    var_dump("push user into \$sessionContainer",
                        $sessionContainer->offsetGet("user"));
                    $this->redirect()->toUrl('/');
                }
            }
        }
        //add variablesContainer to viewModel
        $variablesContainer = array();
        $variablesContainer["controller"] = 'BackEnd\Controller\AuthController\loginAction';

        $variablesContainer['loginForm'] = $this->loginForm;

        $view = new ViewModel($variablesContainer);
        return $view;
    }

    public function joinAction(){
        $variablesContainer = array();
        $variablesContainer["controller"] =
            'BackEnd\Controller\AuthController\joinAction';
        $view = new ViewModel($variablesContainer);
        return $view;
    }

    public function logoutAction(){
        $sessionManager = new SessionManager();
        $sessionStorage = new SessionArrayStorage();
        $sessionManager->setStorage($sessionStorage);
        $sessionContainer =
            new Container(UniMedia::SESSION_CONTAINER, $sessionManager);
        $sessionContainer->offsetUnset("user");
        var_dump("unset user from \$sessionContainer");
        return $this->redirect()->toUrl('/login');
    }
}