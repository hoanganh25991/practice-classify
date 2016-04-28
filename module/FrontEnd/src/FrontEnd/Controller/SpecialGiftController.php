<?php
namespace FrontEnd\Controller;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class SpecialGiftController extends AbstractActionController{
    public function indexAction(){
        return new ViewModel(array('controller' => 'SpecialGiftController'));
    }

//    public function indexAction(){
//        return new ViewModel(array('controller' => 'SpecialGiftController'));
//    }
}