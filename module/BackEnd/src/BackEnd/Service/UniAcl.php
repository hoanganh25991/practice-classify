<?php
namespace BackEnd\Service;

use Zend\Permissions\Acl\Acl;

/**
 * Class UniAcl
 * @package BackEnd\Service
 *
 * UNIACL RULE
 * role on controller action, beside of that
 * each role has its own "special"
 * "special" is a specific controler-action,
 * "special" bcs it not effected by inherit|level
 *
 * role has its own "special"
 * user also has its own "special", not effect by role ^^
 */
class UniAcl{
    const CONFIG = "UNI_ACL_CONFIG";
    const ROLE = "ROLE";
    const CONTROLLER_ACTION = "CONTROLLER_ACTION";
    const ACTION = "ACTION";
    const SPECIAL = "SPECIAL";
    const MAP_ROLE_CONTROLLER_ACTION = "MAP_ROLE_CONTROLLER";
    const MAP_ROLE_SPECIAL = "MAP_ROLE_SPECIAL";
    const MAP_USER_SPECIAL = "MAP_USER_SPECIAL";
    const ROLE_CONTROLLER_ACTION = "ROLE_CONTROLLER_ACTION";
    const ROLE_SPECIAL = "ROLE_SPECIAL";
    const USER_SPECIAL = "USER_SPECIAL";
    /** @var  array */
    protected $config;
    protected $roleControllerActionAcl;
    protected $roleSpecialAcl;
    protected $userSpecialAcl;

    protected $roleArray;
    protected $controllerAction;

    /*
     * ROLE => array(
            "guest" => null,
            "staff" => "guest",
            "editor" => "staff",
            "admin" => "editor"
        ),

        CONTROLLER => array(
            "FrontEnd\Controller\User",
            "BackEnd\Controller\Auth",
        ),

        MAP_ROLE_CONTROLLER => array(
            "guest" => array(
                "controller" => array(action1, action2, action3),
                "controller" => null,
            )
        ),

        MAP_ROLE_SPECIAL => array(
            "guest" => array(
                "controller" => array(action1),
            )
        ),

        MAP_USER_SPECIAL => array(
            "1" => array(
                "controller" => array(action1),
            )
        )
     */
    public function __construct(array $config){
        $this->config = $config;
        $this->roleControllerActionAcl = new Acl();
        $this->roleSpecialAcl = new Acl();
        $this->userSpecialAcl = new Acl();
    }


    /**
     * INIT
     *
     */
    public function init(){
        /**
         * ADD ROLE
         * ADD CONTROLLER
         * MAP ROLE CONTROLLER
         */
        /*
         * ROLE => array(
                "guest" => null,
                "staff" => "guest",
                "editor" => "staff",
                "admin" => "editor"
            ),
         */
        if(isset($this->config[self::ROLE])){
            foreach($this->config[self::ROLE] as $role => $inherit){
                var_dump($role, $inherit);
                $this->roleControllerActionAcl->addRole($role, $inherit);
            }
        }
        /*
         * CONTROLLER_ACTION => array(
                "FrontEnd\Controller\User" => array(view, edit, add, delete, index),
                "BackEnd\Controller\Auth" => array(login, logout, join),
            ),
         */
        if(isset($this->config[self::CONTROLLER_ACTION])){
            foreach($this->config[self::CONTROLLER_ACTION] as $controller => $action){
                $this->roleControllerActionAcl->addResource($controller);
            }
        }
        /*
         * MAP_ROLE_CONTROLLER => array(
                "guest" => array(
                    "controller" => array(action1, action2, action3),
                    "controller" => null,
                )
            ),
         */
        if(isset($this->config[self::MAP_ROLE_CONTROLLER_ACTION])){
            foreach($this->config[self::MAP_ROLE_CONTROLLER_ACTION] as $role => $controllerAction){
                foreach($controllerAction as $controller => $action){
                    $this->roleControllerActionAcl->allow($role, $controller, $action);
                }
            }
        }
        /**
         * MAP ROLE SPECIAL
         */
        /*
         * MAP_ROLE_SPECIAL => array(
                "guest" => array(
                    "controller" => array(action1),
                )
            ),
         */
        if(isset($this->config[self::CONTROLLER_ACTION])){
            foreach($this->config[self::CONTROLLER_ACTION] as $controller => $action){
                $this->roleSpecialAcl->addResource($controller);
            }
        }
        if(isset($this->config[self::MAP_ROLE_SPECIAL])){
            foreach($this->config[self::MAP_ROLE_SPECIAL] as $role => $controllerAction){
                foreach($controllerAction as $controller => $action){
                    $this->roleSpecialAcl->addRole($role);
                    $this->roleSpecialAcl->allow($role, $controller, $action);
                }
            }
        }
        /**
         * ADD USER_ID as ROLE
         * MAP USER SPECIAL
         */
        /*
         * MAP_USER_SPECIAL => array(
                "user_id_1" => array(
                    "controller" => array(action1),
                )
            )
         */
        if(isset($this->config[self::CONTROLLER_ACTION])){
            foreach($this->config[self::CONTROLLER_ACTION] as $controller => $action){
                $this->userSpecialAcl->addResource($controller);
            }
        }
        if(isset($this->config[self::MAP_USER_SPECIAL])){
            foreach($this->config[self::MAP_USER_SPECIAL] as $role => $controllerAction){
                foreach($controllerAction as $controller => $action){
                    $role = "user_id_" . $role;
                    $this->userSpecialAcl->addRole($role);
                    $this->userSpecialAcl->allow($role, $controller, $action);
                }
            }
        }
        /**
         * HANDLE FALLBACK
         * when isset on key, false
         */
        /*
         * case 1: no config @@
         * add default role, "admin", "guest"
         * allow "admin" on ALL controller, action
         */
        if(!isset($this->config[self::ROLE])){
            $this->roleControllerActionAcl->addRole("admin");
            $this->roleControllerActionAcl->allow("admin", null, null);
        }
        /*
         * case *: exception from "Acl"
         */
    }

    /**
     * CONTROLLER ON ROLE
     * @param string $role
     * @return array
     */
    public function getAllInheritOnRole($role){
        $map = $this->config[self::MAP_ROLE_CONTROLLER_ACTION];
        //reset rolleArray
        //previous call may add value into roleArray
        $this->roleArray = array();
        $this->loopParent($role);

        //after loopInherit
        //roleArray has stored roles which this role inherits

        //get all controller action from role by map
        //        $controllerAction = array();
        //        foreach($this->roleArray as $role){
        //            foreach($map[$role] as $controllerAction){
        //                $controllerAction[$role] = $controllerAction;
        //            }
        //        }

        return $this->roleArray;
    }

    /*
     * GET ALL ROLES which this role inherits
     * loop forward to get all parent roles
     */
    private function loopParent($role){
        /*
         * if parent role not exist add it into roleArry
         * avoid duplicate
         */
        if(!array_key_exists($role, $this->roleArray)){
            $this->roleArray[] = $role;
        }
        /*
         * get role which this role inherit
         */
        /** @var array|string|null $inherit */
        $inherit = $this->config[self::ROLE][$role];

        if(is_null($inherit)){
            return;
        }
        if(is_string($inherit)){
            if(!array_key_exists($inherit, $this->roleArray)){
                $this->roleArray[] = $inherit;
            }
            $this->loopParent($inherit);
        }
        if(is_array($inherit)){
            //this role has MANY|ARRAY child
            foreach($inherit as $singleInherit){
                if(!array_key_exists($singleInherit, $this->roleArray)){
                    $this->roleArray[] = $singleInherit;
                }
                $this->loopParent($singleInherit);
            }
        }
    }

    /**
     * @param $role
     */
    public function loopInherit($role){
        foreach($this->roleControllerActionAcl->getRoles() as $inheritRole){
            if($this->roleControllerActionAcl->inheritsRole($inheritRole, $role)){
                $this->roleArray[] = $inheritRole;
            }
        }
    }

    /**
     * SPECIAL ON ROLE
     * @param string $role
     * @return array
     */
    public function getSpecialOnRole($role){
        $map = $this->config[self::MAP_ROLE_SPECIAL];

        //return controller action on role
        return $map[$role];
    }

    /**
     * SPECIAL ON USER
     * @param string $userId
     * @return array
     */
    public function getSpecialOnUser($userId){
        $map = $this->config[self::MAP_USER_SPECIAL];

        //return controller action on role
        return $map[$userId];
    }
    /** @WARN string|null $user["role"]
     * when it return null
     * >>> allow on all role, controller action
     */
    /**
     * CHECK ACCESS CONTROLL LIMIT
     * @param array $user
     * @param string $controller
     * @param string $action
     * @return bool
     *
     * @WARN user may be an empty array
     */
    public function isUniAllowed($user, $controller, $action){
        /**
         * config has NO ROLE
         */
        //add role for user
        if(!isset($this->config[self::ROLE])){
            $user["role"] = "admin";
        }
        /**
         * config has ROLE
         * unlogged|logged user without role, is "guest"
         */
        if(isset($this->config[self::ROLE])){
            if(!isset($user["role"])){
                $user["role"] = "guest";
            }
        }
        var_dump($user);
        /**
         * CHECK HAS ROLE FIRST
         * nothing ensure role of user is loaded into acl
         * corrupt, cache not update,...
         */
        /**
         * CHECK AUL on ROLE CONTROLLER ACTION
         */
        if($this->roleControllerActionAcl->hasRole($user["role"])){
            if($this->roleControllerActionAcl->isAllowed($user["role"], $controller, $action)){
                return true;
            }
        }
        /**
         * CHECK AUL on ROLE SPECIAL
         */
        //by default "map role controller action" always has
        //but "map role special" may not
        if($this->roleSpecialAcl->hasRole($user["role"])){
            if($this->roleSpecialAcl->isAllowed($user["role"], $controller, $action)){
                return true;
            }
        }
        /**
         * CHECK AUL on USER SPECIAL
         * only check on loged user
         */
        /** @WARN not added role
         * isAllowed by default through exception on not added role
         * but when check user special where "user id" as role
         * this id may not added*/
        if(isset($user["id"])){
            /**
             * @WARN role on user id need explicit compile
             */
            $role = "user_id_" . $user["id"];
            if($this->userSpecialAcl->hasRole($role)){
                if($this->userSpecialAcl->isAllowed($user["id"], $controller, $action)){
                    return true;
                }
            }
        }
        return false;
    }



    public function uniDeny($role, $controller, $privilege, $typeAcl){
        if($typeAcl === self::ROLE_CONTROLLER_ACTION){
            /**
             * editor deny from roleA >>> guest also deny from roleA
             */
            $this->roleArray = array();
            $this->loopParent($role);
            foreach($this->roleArray  as $parentRole){
                $this->roleControllerActionAcl->deny($parentRole, $controller, $privilege);
            }

            /**
             * editor deny from role A >>> admin NOT deny from
             */
            $this->roleArray = array();
            $this->loopInherit($role);
            foreach($this->roleArray as $inheritRole){
                $this->roleControllerActionAcl->allow($inheritRole, $controller, $privilege);
            }
            return;
        }
        if($typeAcl === self::ROLE_SPECIAL){
            $this->roleSpecialAcl->deny($role, $controller, $privilege);
            return;
        }
        if($typeAcl === self::USER_SPECIAL){
            $this->userSpecialAcl->deny($role, $controller, $privilege);
            return;
        }
    }
}