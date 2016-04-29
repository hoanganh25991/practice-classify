<?php
namespace BackEnd\Controller;

use BackEnd\Database\AclTable;
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
    protected $action;

    public function __construct($serviceManager){
        $this->serviceManager = $serviceManager;
        $this->action = array(
            "view",
            "edit",
            "add",
            "delete"
        );
    }

    /**
     * only handle GET
     * no check GET/POST from request
     * @return ViewModel
     */
    public function viewAction(){
        $view = new ViewModel();

        /** @var UniCache $cache */
        $cache = $this->serviceManager->get("UniCache");
        $uniAclConfig = $cache->getArrayItem(UniAcl::CONFIG);
        $uniAcl = new UniAcl($uniAclConfig);
        $uniAcl->init();

        /*
         * get user from session
         */
        $uniSession = new UniSession();
        $user = $uniSession->get(UniSession::USER, UniSession::USER_LOGGED);

        $userActionOnRole = array();
        foreach($this->action as $action){
            if($uniAcl->isUniAllowed($user, 'BackEnd\Controller\Role', $action)){
                $userActionOnRole[] = $action;
            }
        }

        $view->setVariable("uniAclConfig", $uniAcl->getConfigForUI());
        $view->setVariable("userAction", $userActionOnRole);
        $view->setVariable("allRoles", $uniAcl->getAllRoles());

        return $view;
    }

    public function addAction(){
        $variables = array();
        $variables["controller"] = 'BackEnd\Controller\UserController\addAction';
        $view = new ViewModel($variables);
        return $view;
    }


    /**
     * only handle POST
     * @return JsonModel|ViewModel
     */
    public function editAction(){
        /** @var Request $request */
        $request = $this->getRequest();
        if($request->isPost()){
            /**
             * get data from request
             */
            $postParam = $request->getPost();
            /*
             * parse json from client's ajax request
             */
            $userAction = $postParam->get("userAction");
            $dataArray = json_decode($postParam->get("data"), true);

            /**
             * init uni acl
             * handle change on user, role, controller, action
             */
            /** @var UniCache $cache */
            $cache = $this->serviceManager->get("UniCache");
            $uniAclConfig = $cache->getArrayItem(UniAcl::CONFIG);
            $uniAcl = new UniAcl($uniAclConfig);
            $uniAcl->init();

            /**
             * hanlde changes from request
             */

            //            $dataObject => array(
            //                "ROLE" => "guest",
            //                "INHERIT" => array(
            //                    'FrontEnd\Controller\Index' => array(),
            //                    'BackEnd\Controller\Index' => array()
            //                  ),
            //                "NOT_INHERIT" => array(
            //                    //
            //                  )
            //            );
            /**
             * deny/remove-allow role from "where"
             */
            if($userAction === "deny"){
                $uniAcl->update($dataArray, "deny");
                $uniAcl->update($dataArray, "deny");
            }
            /**
             * allow role to "where"
             */
            if($userAction === "allow"){
                $uniAcl->update($dataArray, "allow");
            }
            /**
             * rebuild controller-action, when NEW controller added
             * bcs for fast init acl
             * read "where" from cache
             * not from application-config
             */
            if($userAction === "rebuildControllerAction"){
                /**
                 * read controller-action from application-config
                 */
                $config = $this->serviceManager->get("config");
                $controllerAction = array();
                $routes = $config["router"]["routes"];
                foreach($routes as $route){
                    $defaults = $route["options"]["defaults"];
                    $controllerAction[$defaults["controller"]][] = $defaults["action"];
                }
                /**
                 * update to uni acl config
                 */
                $uniAclConfig[UniAcl::CONTROLLER_ACTION] = $controllerAction;
                $uniAcl->setConfig($uniAclConfig);
            }
            /**
             * rebuild config after changes
             */
            $newConfig = $uniAcl->buildConfig();
            $cache->setArrayItem(UniAcl::CONFIG, $newConfig);
            /**
             * save into dabase
             */
            /** @var AclTable $aclTable */
            $aclTable = $this->serviceManager->get('AclTable');
            $aclTable->insert($newConfig);

            $view = new JsonModel();
            $view->setVariable("info", $uniAcl->getConfigForUI());
            return $view;
        }
        /**
         * do no thing for GET method
         */
        die("permission deny");
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