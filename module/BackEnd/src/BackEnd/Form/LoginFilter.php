<?php
namespace BackEnd\Form;

use Zend\InputFilter\InputFilter;
use Zend\Validator;

class LoginFilter extends InputFilter{
  public function __construct(){
    $isEmpty = Validator\NotEmpty::IS_EMPTY;
    $invalidEmail = Validator\EmailAddress::INVALID_FORMAT;

    $this->add(array(
      'name' => 'email',
      'required' => true,
      'filters' => array(
        array(
          'name' => 'StripTags'
        ),
        array(
          'name' => 'StringTrim'
        )
      ),
      'validators' => array(
        array(
          'name' => 'NotEmpty',
          'options' => array(
            'messages' => array(
              $isEmpty => 'Email can not be empty.'
            )
          ),
          'break_chain_on_failure' => true
        ),
        array(
          'name' => 'EmailAddress',
          'options' => array(
            'messages' => array(
              $invalidEmail => 'Enter Valid Email Address.'
            )
          )
        )
      )
    ));

    $this->add(array(
      'name' => 'password',
      'required' => true,
      'filters' => array(
        array(
          'name' => 'StripTags'
        ),
        array(
          'name' => 'StringTrim'
        )
      ),
      'validators' => array(
        array(
          'name' => 'NotEmpty',
          'options' => array(
            'messages' => array(
              $isEmpty => 'Password can not be empty.'
            )
          )
        )
      )
    ));
  }
}