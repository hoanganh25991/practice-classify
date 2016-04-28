<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'FrontEnd\Controller\Keep' => 'FrontEnd\Controller\KeepController',
            'FrontEnd\Controller\Calm' => 'FrontEnd\Controller\CalmController',
            'FrontEnd\Controller\Try' => 'FrontEnd\Controller\TryController',
            'FrontEnd\Controller\Hard' => 'FrontEnd\Controller\HardController',
            'FrontEnd\Controller\SpecialGift' => 'FrontEnd\Controller\SpecialGiftController'
        ),
        'factories' => array(
            'FrontEnd\Controller\Index' => 'FrontEnd\Factory\IndexControllerFactory',
        )
    ),
    'router' => array(
        'routes' => array(
            'home' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/',
                    'defaults' => array(
                        'controller' => 'FrontEnd\Controller\Index',
                        'action' => 'index',
                    ),
                ),
            ),
            'keep' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/keep',
                    'defaults' => array(
                        'controller' => 'FrontEnd\Controller\Keep',
                        'action' => 'index',
                    ),
                ),
            ),
//            'keep/calm' => array(
//                'type' => 'literal',
//                'options' => array(
//                    'route' => '/keep/calm',
//                    'defaults' => array(
//                        'controller' => 'FrontEnd\Controller\Keep',
//                        'action' => 'calm',
//                    ),
//                ),
//            ),
            'calm' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/calm',
                    'defaults' => array(
                        'controller' => 'FrontEnd\Controller\Calm',
                        'action' => 'index',
                    ),
                ),
            ),
            'try' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/try',
                    'defaults' => array(
                        'controller' => 'FrontEnd\Controller\Try',
                        'action' => 'index',
                    ),
                ),
            ),
            'hard' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/hard',
                    'defaults' => array(
                        'controller' => 'FrontEnd\Controller\Hard',
                        'action' => 'index',
                    ),
                ),
            ),
            'special-gift' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/special-gift',
                    'defaults' => array(
                        'controller' => 'FrontEnd\Controller\SpecialGift',
                        'action' => 'index',
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        //        'not_found_template' => 'error/404',
        //        'exception_template' => 'error/index',
        // Doctype with which to seed the Doctype helper
        'doctype' => 'HTML5',
        // e.g. HTML5, XHTML1

        // Layout template name
        'layout' => 'FrontEnd\Layout',
        // e.g. 'layout/layout'

        // TemplateMapResolver configuration
        // template/path pairs
        'template_map' => array(
            'FrontEnd\Layout' => __DIR__ . '/../view/layout/layout.phtml',
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
);
