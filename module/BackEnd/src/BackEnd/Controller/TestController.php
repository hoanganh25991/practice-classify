<?php
namespace BackEnd\Controller;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class TestController extends AbstractActionController{
    public function indexAction(){
        return new ViewModel(array("controller" => 'BackEnd\Controller\TestController'));
    }
}