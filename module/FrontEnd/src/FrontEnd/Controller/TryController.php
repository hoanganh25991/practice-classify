<?php
namespace FrontEnd\Controller;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class TryController extends AbstractActionController{
    public function indexAction(){
        return new ViewModel(array('controller' => 'TryController'));
    }
}