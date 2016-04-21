<?php
namespace BackEnd\Controller;

use BackEnd\Model\UniAclConfigModel;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\ServiceManager\ServiceManager;
use Zend\View\Model\ViewModel;

class UserController extends AbstractActionController{
    /** @var  ServiceManager $serviceManager */
    protected $serviceManager;

    public function __construct($serviceManager){
        $this->serviceManager = $serviceManager;
    }

    public function viewAction(){
        /**
         * who i am ^^
         */
        $variables = array();
        $variables["controller"] = 'BackEnd\Controller\UserController\viewAction';

        /**
         * handle logic
         */
        /**
         * loop role + resource + privilege
         * loop role + special
         */
        /**
         * base on role, where user can access
         */
        $uniAclConfigModel = new UniAclConfigModel($this->serviceManager);
        //        $guestResourcePrivilege = $uniAclConfigModel->getResourcePrivilegeOnRole("guest");
        //        $editorResourcePrivilege = $uniAclConfigModel->getResourcePrivilegeOnRole("editor");
        //        $adminResourcePrivilege = $uniAclConfigModel->getResourcePrivilegeOnRole("admin");
        //        $variables["guest"] = $guestResourcePrivilege;
        //        $variables["editor"] = $editorResourcePrivilege;
        //        $variables["admin"] = $adminResourcePrivilege;
        $userResourcePrivilege = $uniAclConfigModel->getControllerActionOnUser();
        $variables["userResourcePrivilege"] = $userResourcePrivilege;
        /**
         * /admin/user >>> map user---resource
         * handle view/edit/add/delete on resource
         * resource loop from where $user allow
         */
        $thisController = 'BackEnd\Controller\User';
        $actionOnController = $uniAclConfigModel->getActionOnController($thisController);
        $variables["actionOnController"] = $actionOnController;
        /**
         * inject $view into layout
         */
        $view = new ViewModel($variables);
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