<?php
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
                    'route' => '/admin/user/add',
                    'defaults' => array(
                        'controller' => 'BackEnd\Controller\Role',
                        'action' => 'add',
                    ),
                ),
            ),
            'user-edit' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/admin/user/edit',
                    'defaults' => array(
                        'controller' => 'BackEnd\Controller\Role',
                        'action' => 'edit',
                    ),
                ),
            ),
            'user-delete' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/admin/user/delete',
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
    )
);
