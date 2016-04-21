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
        /*
         * read from "storage"
         * hien thi ra view
         */

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