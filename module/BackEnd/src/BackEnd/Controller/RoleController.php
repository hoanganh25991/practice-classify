<?php
namespace BackEnd\Controller;

use BackEnd\Service\UniAcl;
use BackEnd\Service\UniCache;
use BackEnd\Service\UniSession;
use Zend\Http\Request;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\ServiceManager\ServiceManager;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class RoleController extends AbstractActionController{
    /** @var  ServiceManager $serviceManager */
    protected $serviceManager;
    protected $roleAction;

    public function __construct($serviceManager){
        $this->serviceManager = $serviceManager;
        $this->roleAction = array("view", "edit", "add", "delete");
    }

    public function viewAction(){
        /** @var Request $request */

        $request = $this->getRequest();
        if($request->isPost()){
            $postParam = $request->getPost();
            $userAction = $postParam->get("userAction");
            $data = $postParam->get("data");
            /**
             * HANDLE data, ask some one for help
             */


//            var_dump($data, $userAction);
            $view = new JsonModel();
//            $view->setVariable("info", $data);
            return $view;
        }
        $view = new ViewModel();
        $view->setVariable("controller", 'BackEnd\Controller\User\viewAction');
        /*
         * read from "storage"
         * hien thi ra view
         */
        /**
         * inject $uniAclConfig into view
         * view handle
         * done
         */
        /** @var UniCache $cache */
        $cache = $this->serviceManager->get("UniCache");
        $uniAclConfig = $cache->getArrayItem(UniAcl::CONFIG);
        $uniAcl = new UniAcl($uniAclConfig);
        $uniAcl->sinit();

        /**
         * GET USER FROM SESSION
         */
        $uniSession = new UniSession();
        $user = $uniSession->get(UniSession::USER, UniSession::USER_LOGGED);
        $user["role"] = "admin";
        $userActionOnRole = array();
        foreach($this->roleAction as $action){
            if($uniAcl->isUniAllowed($user, 'BackEnd\Controller\Role', $action)){
                $userActionOnRole[] = $action;
            }
        }
//        $uniAclConfig["ROLE"] = array(
//            "guest" => null,
//            "editor" => "staff",
//            "admin" => "editor"
//        );
//        $uniAclConfig["MAP_ROLE_CONTROLLER"] = array(
//            "guest" => array(
//                "FrontEnd\C" => array(action1, action2, action3),
//                "controller" => null,
//            )
//        ),
        $view->setVariable("admin", $uniAcl->getAllOnRole("admin"));
//        $view->setVariable("guest", $uniAcl->getWhereOnRole("guest"));
//        $view->setVariable("editor", $uniAcl->getWhereOnRole("editor"));
        $view->setVariable("uniAclConfig", $uniAclConfig);
        $view->setVariable("userActionOnRole", $userActionOnRole);
        $view->setVariable("inheritRole", $uniAclConfig[UniAcl::MAP_ROLE_PARENT]);
        $view->setVariable("allRoles", $uniAcl->getAllRoles());
        $view->setVariable("allControllerAction", $uniAclConfig[UniAcl::CONTROLLER_ACTION]);
        $view->setVariable("mapRoleWhere", $uniAcl->getRoleWhere());

        return $view;
    }

    public function addAction(){
        $variables = array();
        $variables["controller"] = 'BackEnd\Controller\UserController\addAction';
        $view = new ViewModel($variables);
        return $view;
    }


    public function editAction(){
        $variables = array();
        $variables["controller"] = 'BackEnd\Controller\UserController\editAction';
        $view = new ViewModel($variables);
        return $view;
    }

    public function deleteAction(){
        $variables = array();
        $variables["controller"] = 'BackEnd\Controller\UserController\deleteAction';
        $view = new ViewModel($variables);
        return $view;
    }

    public function hopeyouworkAction(){
        $variables = array();
        $variables["controller"] = 'BackEnd\Controller\UserController\hopeyouworkAction';
        $view = new ViewModel($variables);
        return $view;
    }
}