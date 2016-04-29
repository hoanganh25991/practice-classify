<?php
namespace BackEnd\Controller;

use BackEnd\Database\UserTable;
use BackEnd\Service\UniAcl;
use BackEnd\Service\UniCache;
use Zend\Http\Request;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\ServiceManager\ServiceManager;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class UserController extends AbstractActionController{
    protected $serviceManager;

    /**
     * UserController constructor.
     * @param ServiceManager $sm
     */
    public function __construct($sm){
        $this->serviceManager = $sm;
    }

    public function addAction(){
        /** @var Request $request */
        $request = $this->getRequest();
        $cache = $this->serviceManager->get("UniCache");
        $uniAclConfig = $cache->getArrayItem(UniAcl::CONFIG);
        $uniAcl = new UniAcl($uniAclConfig);
        $uniAcl->init();
        /** @var UserTable $userTable */
        $userTable = $this->serviceManager->get('UserTable');


        if($request->isGet()){
            $view = new ViewModel();
            $view->setVariable("allRoles", $uniAcl->getAllRoles());
            $allUsers = $userTable->getAll();
            $view->setVariable("allUsers", $allUsers);
            return $view;
        }
        if($request->isPost()){
            $view = new JsonModel();
            $postParam = $request->getPost();
            $userAction = $postParam->get("userAction");
            $data = json_decode($postParam->get("data"), true);

            if($userAction === "addUserRole"){
                /**
                 * save to table "user"
                 */
                //map user-role only in user_role table
                $userTable->insertIdRole($data["user"], $data["role"]);
                $txt = sprintf("user [%s] has added role [%s]", $data["user"], $data["role"]);
                $view->setVariable("info", $txt);
            }
            return $view;
        }


        return $view;
    }
}