<?php
namespace BackEnd\Form;

use Zend\Form\Form;

class LoginForm extends Form{
    public function __construct($name){
        parent::__construct($name);
        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'email',
            'type' => 'text',
            'attributes' => array(
                'id' => 'email',
                'class' => 'form-control',
                'placeholder' => 'example@example.com'
            ),
            'options' => array(
                'label' => 'Email',
            )
        ));

        $this->add(array(
            'name' => 'password',
            'type' => 'password',
            'attributes' => array(
                'id' => 'password',
                'class' => 'form-control',
                'placeholder' => '**********'
            ),
            'options' => array(
                'label' => 'Password',
            )
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Csrf',
            'name' => 'loginCsrf',
            'options' => array(
                'csrf_options' => array(
                    'timeout' => 3600
                )
            )
        ));

        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Submit',
                'class' => 'btn btn-success'
            )
        ));
    }
}