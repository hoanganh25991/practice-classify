<?php
\BackEnd\Service\UniAcl::CONFIG => array(
        \BackEnd\Service\UniAcl::ROLE_INHERIT => array(
            'guest' => null,
            'editor' => 'guest',
            'admin' => 'editor',
        ),
        /**
         * role => (resource => privilege)
         * allow($role, $resource, $privilege)
         * where $privilege is a FILTER on $resource
         */
        \BackEnd\Service\UniAcl::MAP_ROLE_CONTROLLER_ACTION => array(
            'guest' => array(
                'FrontEnd\Controller\Index' => array('index'),
                'FrontEnd\Controller\Keep' => array('index'),
                'FrontEnd\Controller\Calm' => array('index'),
                'FrontEnd\Controller\Try' => array('index'),
                'FrontEnd\Controller\Hard' => array('index'),
                'BackEnd\Controller\Auth' => array(
                    'login',
                    'logout'
                ),
            ),
            'editor' => array(
                'BackEnd\Controller\User' => array(
                    'view',
                    'add',
                    'edit'
                )
            ),
            'admin' => array(
                'BackEnd\Controller\User' => array('delete'),
            )
        ),
        /**
         * role => resource.privilege
         * map directly from role to resource.privilege
         * check by array(key) === value
         */
        \BackEnd\Service\UniAcl::MAP_ROLE_SPECIAL => array(
            'editor' => 'FrontEnd\Controller\SpecialGift\index',
        ),
        /**
         * @warn these name NEED store in Unimedia as static/const
         */
        \BackEnd\Service\UniAcl::MAP_USER_SPECIAL => array(
            '1' => 'admin',
            '2' => 'editor',
            '3' => 'guest'
        ),
        /**
         * user => resource.privilege
         * map directly from user to resource.privilege
         * check by array(key) === value
         */
        'mapUserIdSpecial' => array(
            '3' => 'BackEnd\Controller\User\view'
        ),
    ),
),