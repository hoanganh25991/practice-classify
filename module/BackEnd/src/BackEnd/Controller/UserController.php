<?php
namespace BackEnd\Controller;

use BackEnd\Database\UserTable;
use BackEnd\Service\UniAcl;
use BackEnd\Service\UniCache;
use Zend\Http\Request;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\ServiceManager\ServiceManager;
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
        $view = new ViewModel();
        /** @var Request $request */
        $request = $this->getRequest();
        $cache = $this->serviceManager->get("UniCache");
        $uniAclConfig = $cache->getArrayItem(UniAcl::CONFIG);
        $uniAcl = new UniAcl($uniAclConfig);
        $uniAcl->init();
        $view->setVariable("allRoles", $uniAcl->getAllRoles());

        /** @var UserTable $userTable */
        $userTable = $this->serviceManager->get('UserTable');
        $allUsers = $userTable->getAll();
        $view->setVariable("allUsers", $allUsers);


        if($request->isGet()){
            return $view;
        }
        if($request->isPost()){
            $postParam = $request->getPost();
            /** @var UniCache $cache */
            $cache = $this->serviceManager->get("UniCache");
            $uniAclConfig = $cache->getArrayItem(UniAcl::CONFIG);
            $uniAcl = new UniAcl($uniAclConfig);
            $uniAcl->init();
            $view->setVariable("allRoles", $uniAcl->getAllRoles());
        }



        return $view;
    }
}