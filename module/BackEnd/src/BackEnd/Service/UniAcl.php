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
class UniAcl extends Acl{
    const CONFIG = "UNI_ACL_CONFIG";
    const ROLE = "ROLE";
    const CONTROLLER_ACTION = "CONTROLLER_ACTION";
    const ACTION = "ACTION";
    const SPECIAL = "SPECIAL";
    const MAP_ROLE_CONTROLLER = "MAP_ROLE_CONTROLLER";
    const MAP_ROLE_SPECIAL = "MAP_ROLE_SPECIAL";
    const MAP_USER_SPECIAL = "MAP_USER_SPECIAL";

    /** @var  array */
    protected $config;
    protected $roleControllerAcl;
    protected $roleSpecialAcl;
    protected $userSpecialAcl;

    protected $roleArray;

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
        $this->roleControllerAcl = new Acl();
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
                $this->roleControllerAcl->addRole($role, $inherit);
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
                $this->roleControllerAcl->addResource($controller);
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
        if(isset($this->config[self::MAP_ROLE_CONTROLLER])){
            foreach($this->config[self::MAP_ROLE_CONTROLLER] as $role => $controllerAction){
                foreach($controllerAction as $controller => $action){
                    $this->roleControllerAcl->allow($role, $controller, $action);
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
        if(isset($this->config[self::MAP_ROLE_SPECIAL])){
            foreach($this->config[self::MAP_ROLE_SPECIAL] as $role => $controllerAction){
                foreach($controllerAction as $controller => $action){
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
                "1" => array(
                    "controller" => array(action1),
                )
            )
         */
        if(isset($this->config[self::MAP_USER_SPECIAL])){
            foreach($this->config[self::MAP_USER_SPECIAL] as $role => $controllerAction){
                foreach($controllerAction as $controller => $action){
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
            $this->roleControllerAcl->addRole("admin");
            $this->roleControllerAcl->addRole("guest");
            $this->roleControllerAcl->allow("admin", null, null);
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
    public function getControllerActionOnRole($role){
        $map = $this->config[self::MAP_ROLE_CONTROLLER];
        //reset rolleArray
        //previous call may add value into roleArray
        $this->roleArray = array();
        $this->loopInherit($role);

        //after loopInherit
        //roleArray has stored roles which this role inherits

        //get all controller action from role by map
        $controllerAction = array();
        foreach($this->roleArray as $role){
            foreach($map[$role] as $controllerAction){
                $controllerAction[] = $controllerAction;
            }
        }

        return $controllerAction;
    }

    /*
     * GET ALL ROLES which this role inherits
     * loop forward to get all parent roles
     */
    private function loopInherit($role){
        /*
         * if parent role not exist add it into roleArry
         * avoid duplicate
         */
        if(!array_key_exists($this->roleArray, $role)){
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
            if(!array_key_exists($this->roleArray, $inherit)){
                $this->roleArray[] = $inherit;
            }
            $this->loopInherit($inherit);
        }
        if(is_array($inherit)){
            //this role has MANY|ARRAY child
            foreach($inherit as $singleInherit){
                if(!array_key_exists($this->roleArray, $singleInherit)){
                    $this->roleArray[] = $singleInherit;
                }
                $this->loopInherit($singleInherit);
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
        /*
         * this uni acl on init, config has NOTHING
         * the first logged in user is "admin"
         */
        if(!isset($this->config[self::ROLE])){
            $user["role"] = "admin";
        }else{
            //by default, NOT logged in user is "guest"
            if(count($user) < 0){
                $user["role"] = "guest";
            }
        }

        /**
         * check acl on ROLE CONTROLLER ACTION
         */
        if($this->roleControllerAcl->isAllowed($user["role"], $controller, $action)){
            return true;
        }
        /**
         * check acl on ROLE SPECIAL
         */
        if($this->roleSpecialAcl->isAllowed($user["role"], $controller, $action)){
            return true;
        }
        /**
         * check acl on USER SPECIAL
         */
        /** @WARN not added role
         * isAllowed by default through exception on not added role
         * but when check user special where "user id" as role
         * this id may not added*/
        if($this->userSpecialAcl->hasRole($user["id"])){
            if($this->userSpecialAcl->isAllowed($user["id"], $controller, $action)){
                return true;
            }
        }
        return false;
    }
}