<?php
var_dump(\BackEnd\Service\UniAcl::CONFIG);
return array(
    'controllers' => array(
        'invokables' => array(
            'BackEnd\Controller\Test' => 'BackEnd\Controller\TestController',
            'BackEnd\Controller\TestUni' => 'BackEnd\Controller\TestUniController',
        ),
        'factories' => array(
            'BackEnd\Controller\Auth' => 'BackEnd\Factory\AuthControllerFactory',
            'BackEnd\Controller\Role' => 'BackEnd\Factory\RoleControllerFactory',
        ),
    ),
    'router' => array(
        'routes' => array(
            'backend' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/backend',
                    'defaults' => array(
                        'controller' => 'BackEnd\Controller\Test',
                        'action' => 'index',
                    ),
                ),
            ),
            'backend02' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/backend02',
                    'defaults' => array(
                        'controller' => 'BackEnd\Controller\TestUni',
                        'action' => 'index',
                    ),
                ),
            ),
            'login' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/login',
                    'defaults' => array(
                        'controller' => 'BackEnd\Controller\Auth',
                        'action' => 'login',
                    ),
                ),
            ),
            'join' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/join',
                    'defaults' => array(
                        'controller' => 'BackEnd\Controller\Auth',
                        'action' => 'join',
                    ),
                ),
            ),
            'logout' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/logout',
                    'defaults' => array(
                        'controller' => 'BackEnd\Controller\Auth',
                        'action' => 'logout',
                    ),
                ),
            ),
            'user-view' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/admin/role',
                    'defaults' => array(
                        'controller' => 'BackEnd\Controller\Role',
                        'action' => 'view',
                    ),
                ),
            ),
            'user-add' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/admin/role/add',
                    'defaults' => array(
                        'controller' => 'BackEnd\Controller\Role',
                        'action' => 'add',
                    ),
                ),
            ),
            'user-edit' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/admin/role/edit',
                    'defaults' => array(
                        'controller' => 'BackEnd\Controller\Role',
                        'action' => 'edit',
                    ),
                ),
            ),
            'user-delete' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/admin/role/delete',
                    'defaults' => array(
                        'controller' => 'BackEnd\Controller\Role',
                        'action' => 'delete',
                    ),
                ),
            ),
        ),
    ),
    // ViewManager configuration
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        //        'not_found_template' => 'error/404',
        //        'exception_template' => 'error/index',
        // Doctype with which to seed the Doctype helper
        'doctype' => 'HTML5',
        // e.g. HTML5, XHTML1

        // Layout template name
        'layout' => 'BackEnd\Layout',
        // e.g. 'layout/layout'

        // TemplateMapResolver configuration
        // template/path pairs
        'template_map' => array(
            'BackEnd\Layout' => __DIR__ . '/../view/layout/layout.phtml',
            //            'error/404' => __DIR__ . '/../view/error/404.phtml',
            //            'error/index' => __DIR__ . '/../view/error/index.phtml',
        ),

        // TemplatePathStack configuration
        // module/view script path pairs
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
        // Additional strategies to attach
        // These should be class names or service names of View strategy classes
        // that act as ListenerAggregates. They will be attached at priority 100,
        // in the order registered.
        'strategies' => array(
            'ViewJsonStrategy',
            // register JSON renderer strategy
            'ViewFeedStrategy',
            // register Feed renderer strategy
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            'Session' => 'BackEnd\Factory\SessionManagerFactory',
            'UniCache' => 'BackEnd\Factory\UniCacheFactory',
            'UserTableQuery' => function($sm){
                return new \BackEnd\Database\UserTable($sm);
            },
            'UniAcl' => 'BackEnd\Factory\UniAclFactory',

        )
    ),
    \BackEnd\Service\UniAcl::CONFIG => array(
        \BackEnd\Service\UniAcl::ROLE => array(
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
                'BackEnd\Controller\Role' => array(
                    'view',
                    'add',
                    'edit'
                )
            ),
            'admin' => array(
                'BackEnd\Controller\Role' => null,
            )
        ),
        /**
         * role => resource.privilege
         * map directly from role to resource.privilege
         * check by array(key) === value
         */
        \BackEnd\Service\UniAcl::MAP_ROLE_SPECIAL => array(
            'editor' => array(
                'FrontEnd\Controller\SpecialGift' => array('index')
            )
        ),
        /**
         * @warn these name NEED store in Unimedia as static/const
         */
        \BackEnd\Service\UniAcl::MAP_USER_SPECIAL => array(
            'user_1' => array(
                'FrontEnd\Controller\SpecialGift' => array('index')
            )
        ),
    ),
);
