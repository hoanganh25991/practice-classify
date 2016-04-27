<?php
namespace BackEnd\Service;

use Exception;
use Zend\Permissions\Acl\Acl;

/**
 * Class UniAcl
 * @package BackEnd\Service
 *
 * UNIACL RULE
 * as normal acl for "role" on "controller action"
 *
 * beside of that, "role" can go to "special-place"
 * which not share inherit
 *
 * user also has its own "special"
 * not effect by role ^^
 */
class UniAcl{
    const CONFIG = "UNI_ACL_CONFIG";
    const MAP_ROLE_PARENT = "ROLE_INHERIT";
    const CONTROLLER_ACTION = "CONTROLLER_ACTION";
    const ACTION = "ACTION";
    const SPECIAL = "SPECIAL";
    const MAP_ROLE_CONTROLLER_ACTION = "MAP_ROLE_CONTROLLER_ACTION";
    const MAP_ROLE_SPECIAL = "MAP_ROLE_SPECIAL";
    const MAP_USER_SPECIAL = "MAP_USER_SPECIAL";
    const ROLE_CONTROLLER_ACTION = "ROLE_CONTROLLER_ACTION";
    const ROLE_SPECIAL = "ROLE_SPECIAL";
    const USER_SPECIAL = "USER_SPECIAL";

    const INHERIT = "INHERIT";
    const NOT_INHERIT = "NOT_INHERIT";
    /** @var  array */
    protected $config;
    protected $roleControllerActionAcl;
    protected $roleSpecialAcl;
    protected $userSpecialAcl;

    protected $roleArray;
    protected $controllerAction;

    protected $tempConfig;

    protected $cA;

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
        /**
         * check config before init
         * config must have CONTROLLER_ACTION
         */
        if(!isset($config[self::CONTROLLER_ACTION])){
            throw new Exception('config must have CONTROLLER_ACTION');
        }
        $this->config = $config;
        $this->roleControllerActionAcl = new Acl();
        $this->roleSpecialAcl = new Acl();
        $this->userSpecialAcl = new Acl();
        $this->cA = $this->config[self::CONTROLLER_ACTION];
    }

    /**
     * INIT
     * we have 3 ACL inside UniAcl
     * 1. Role Controller Action
     * 2. Role Special
     * 3. User Special
     */
    public function init(){
        /**
         * ROLE CONTROLLER ACTION
         * 1. add ROLE
         * 2. add CONTROLLER
         * 3. map ROLE CONTROLLER ACTION
         */
        //1. add ROLE
        if(isset($this->config[self::MAP_ROLE_PARENT])){
            $mapRoleParent = $this->config[self::MAP_ROLE_PARENT];
            foreach($mapRoleParent as $role => $parent){
                $this->roleControllerActionAcl->addRole($role, $parent);
            }
        }
        //2. add CONTROLLER
        //config ensure CONTROLLER_ACTION when init
        $this->loadController($this->roleControllerActionAcl);
        //3. map ROLE CONTROLLER ACTION
        if(isset($this->config[self::MAP_ROLE_CONTROLLER_ACTION])){
            $mapRoleControllerAction = $this->config[self::MAP_ROLE_CONTROLLER_ACTION];
            $this->allow($mapRoleControllerAction, $this->roleControllerActionAcl);
        }

        /**
         * ROLE SPECIAL
         * 1. add ROLE
         * 2. add CONTROLLER
         * 3. map ROLE SPECIAL
         */
        //1. add ROLE
        //bcs, ROLE SPECIAL and ROLE CONTROLLER ACTION
        //share the same role in MAP ROLE PARENT
        //ROLE SPECIAL not accept inherit from parent
        $allRoles = $this->getAllRoles();
        if(count($allRoles) > 0){
            foreach($allRoles as $role){
                $this->roleSpecialAcl->addRole($role);
            }
        }
        //2. add CONTROLLER
        //note: ROLE SPECIAL share same CONTROLLER
        $this->loadController($this->roleSpecialAcl);
        //3. map ROLE SPECIAL
        if(isset($this->config[self::MAP_ROLE_SPECIAL])){
            $mapRoleSpecial = $this->config[self::MAP_ROLE_SPECIAL];
            $this->allow($mapRoleSpecial, $this->roleSpecialAcl);
        }
        /**
         * USER SPECIAL
         * 1. add ROLE
         * 2. add CONTROLLER
         * 3. map USER SPECIAL
         */
        //1. add ROLE
        //user email as role in USER SPECIAL
        //add role base on map USER SPECIAL
        if(isset($this->config[self::MAP_USER_SPECIAL])){
            $mapUserSpecial = $this->config[self::MAP_USER_SPECIAL];
            foreach($mapUserSpecial as $role){
                $this->userSpecialAcl->addRole($role);
            }
        }
        //2. add CONTROLLER
        //note: USER SPECIAL share same CONTROLLER
        $this->loadController($this->userSpecialAcl);
        //3. map USER CONTROLLER
        if(isset($this->config[self::MAP_USER_SPECIAL])){
            $mapUserSpecial = $this->config[self::MAP_USER_SPECIAL];
            $this->allow($mapUserSpecial, $this->userSpecialAcl);
        }

    }

    /**
     * @param Acl $acl
     */
    private function loadController($acl){
        $controllerArray = array_keys($this->config[self::CONTROLLER_ACTION]);
        foreach($controllerArray as $controller){
            $acl->addResource($controller);
        }
    }

    /**
     * @param array $mapRoleControllerAction
     * @param Acl $acl
     */
    private function allow($mapRoleControllerAction, $acl){
        foreach($mapRoleControllerAction as $role => $controllerAction){
            foreach($controllerAction as $controller => $actionArray){
                foreach($actionArray as $action){
                    $acl->allow($role, $controller, $action);
                }
            }
        }
    }

    /**
     * @return array $allRoles
     */
    public function getAllRoles(){
        $allRoles = array();
        if(isset($this->config[self::MAP_ROLE_PARENT])){
            $mapRoleParent = $this->config[self::MAP_ROLE_PARENT];
            $allRoles = array_keys($mapRoleParent);
            return $allRoles;
        }
        return $allRoles;
    }

    /**
     * GET WHERE ON ROLE at ACL
     */
    /**
     * @param string $role
     * @param Acl $acl
     * @return array $result
     */
    public function getWhereOnRoleAcl($role, $acl){
        $result = array();
        foreach($this->cA as $controller => $actionArray){
            foreach($actionArray as $action){
                if($acl->isAllowed($role, $controller, $action)){
                    $result[$controller] = $action;
                }
            }
        }
        return $result;
    }

    /**
     * @warn $acl need tell it TYPE
     * @param $role
     * @param $acl
     * @param $newConfig
     */
    public function updateRoleControllerAction($role, $acl, $newConfig){
        $oldConfig = $this->getWhereOnRoleAcl($role, $acl);
        $whereDeny = $this->arrayRecursiveDiff($oldConfig, $newConfig);
        foreach($whereDeny as $controller => $action){
            $this->uniDeny($role, $controller, $action, self::ROLE_CONTROLLER_ACTION);
        }
    }

    /** @warn $acl need tell it TYPE
     * @param $role
     * @param $acl
     * @param $newConfig
     */
    public function updateRoleSpecial($role, $acl, $newConfig){
        $oldConfig = $this->getWhereOnRoleAcl($role, $acl);
        $whereDeny = $this->arrayRecursiveDiff($oldConfig, $newConfig);
        foreach($whereDeny as $controller => $action){
            $this->uniDeny($role, $controller, $action, self::ROLE_SPECIAL);
        }
    }

    /**
     * ANY CHANGE NEED BUILD CONFIG
     * into init config
     */


    /**
     * INIT
     *
     */
    public function sinit(){
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
        if(isset($this->config[self::MAP_ROLE_PARENT])){
            foreach($this->config[self::MAP_ROLE_PARENT] as $role => $inherit){
                //                var_dump($role, $inherit);
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
        if(!isset($this->config[self::MAP_ROLE_PARENT])){
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
        $this->loopResource($role);

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
    private function loopResource($role){
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
        $inherit = $this->config[self::MAP_ROLE_PARENT][$role];

        if(is_null($inherit)){
            return;
        }
        if(is_string($inherit)){
            if(!array_key_exists($inherit, $this->roleArray)){
                $this->roleArray[] = $inherit;
            }
            $this->loopResource($inherit);
        }
        if(is_array($inherit)){
            //this role has MANY|ARRAY child
            foreach($inherit as $singleInherit){
                if(!array_key_exists($singleInherit, $this->roleArray)){
                    $this->roleArray[] = $singleInherit;
                }
                $this->loopResource($singleInherit);
            }
        }
    }

    public function loopParent($role){
        $this->roleArray = array();
        foreach($this->roleControllerActionAcl->getRoles() as $parentRole){
            if($this->roleControllerActionAcl->inheritsRole($role, $parentRole)){
                $this->roleArray[] = $parentRole;
            }
        }
    }

    /**
     * @param $role
     */
    public function loopInherit($role){
        $this->roleArray = array();
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
        if(!isset($this->config[self::MAP_ROLE_PARENT])){
            $user["role"] = "admin";
        }
        /**
         * config has ROLE
         * unlogged|logged user without role, is "guest"
         */
        if(isset($this->config[self::MAP_ROLE_PARENT])){
            if(!isset($user["role"])){
                $user["role"] = "guest";
            }
        }
        //        var_dump($user);
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
            if(!$this->roleControllerActionAcl->isAllowed($role, $controller, $privilege)){
                return;
            }
            $this->roleControllerActionAcl->deny($role, $controller, $privilege);
            /**
             * editor deny from roleA >>> guest also deny from roleA
             */
            $this->roleArray = array();
            $this->loopParent($role);
            foreach($this->roleArray as $parentRole){
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

    public function buildConfig(){
        $this->tempConfig = array();
        /**
         * build CONTROLLER ACTION
         */
        $this->tempConfig[self::CONTROLLER_ACTION] = $this->config[self::CONTROLLER_ACTION];
        //        var_dump($tempConfig);
        /**
         * buid ROLE
         */
        foreach($this->roleControllerActionAcl->getRoles() as $role){
            $this->roleArray = array();
            $this->loopParent($role);
            /*
             * bcs, parent role by default is null, not empty array
             * convert empty array to null as expect
             */
            if(count($this->roleArray) === 0){
                $this->roleArray = null;
            }
            $this->tempConfig[self::MAP_ROLE_PARENT][$role] = $this->roleArray;
        }
        //        var_dump($tempConfig);
        /**
         * MAP ROLE CONTROLLER ACTION
         */
        /*
         * map role for f1 parent, who not inherit from anyone
         */
        foreach($this->tempConfig[self::MAP_ROLE_PARENT] as $role => $inheritRole){
            if(is_null($inheritRole)){
                $this->f($this->config[self::CONTROLLER_ACTION], $this->roleControllerActionAcl, $role);
            }
        }
        /*
         * map for child
         */
        foreach($this->tempConfig[self::MAP_ROLE_PARENT] as $role => $inheritRole){
            if(is_null($inheritRole)){
            }
            $arrayDiff = $this->config[self::CONTROLLER_ACTION];
            if(is_string($inheritRole)){
                $arrayDiff = $this->arrayRecursiveDiff($arrayDiff,
                    $this->tempConfig[self::MAP_ROLE_CONTROLLER_ACTION][$inheritRole]);
            }
            if(is_array($inheritRole)){
                foreach($inheritRole as $s_inheritRole){
                    $arrayDiff = $this->arrayRecursiveDiff($arrayDiff,
                        $this->tempConfig[self::MAP_ROLE_CONTROLLER_ACTION][$s_inheritRole]);
                }
            }
            //            var_dump($arrayDiff);
            $this->f($arrayDiff, $this->roleControllerActionAcl, $role);
        }
        //        $arrayDiff = $this->config[self::CONTROLLER_ACTION];
        //        $arrayDiff = $this->arrayRecursiveDiff($arrayDiff, $this->tempConfig[self::MAP_ROLE_CONTROLLER_ACTION]["guest"]);
        //        var_dump($arrayDiff);
        //        $this->f($arrayDiff, "editor");
        //        var_dump($this->tempConfig);

        /**
         * map ROLE SPECIAL
         */
        $allRoles = $this->roleControllerActionAcl->getRoles();
        foreach($allRoles as $role){
            if($this->roleSpecialAcl->hasRole($role)){
                $this->r($this->config[self::CONTROLLER_ACTION], $this->roleSpecialAcl, $role);
            }
        }

        /**
         * map USER SPECIAL
         */
        $allRoles = $this->userSpecialAcl->getRoles();
        foreach($allRoles as $role){
            $this->u($this->config[self::CONTROLLER_ACTION], $this->userSpecialAcl, $role);
        }
        //        var_dump($this->tempConfig);
        return $this->tempConfig;
    }

    /**
     * @param $controllerAction
     * @param Acl $typeAcl
     * @param $role
     */
    private function f($controllerAction, $typeAcl, $role){
        foreach($controllerAction as $controller => $actionArray){
            foreach($actionArray as $action){
                //check isAllowed on each (controller, action)
                if($typeAcl->isAllowed($role, $controller, $action)){
                    $this->tempConfig[self::MAP_ROLE_CONTROLLER_ACTION][$role][$controller][] = $action;
                }
            }
        }
    }

    private function r($controllerAction, $typeAcl, $role){
        foreach($controllerAction as $controller => $actionArray){
            foreach($actionArray as $action){
                //check isAllowed on each (controller, action)
                if($typeAcl->isAllowed($role, $controller, $action)){
                    $this->tempConfig[self::MAP_ROLE_SPECIAL][$role][$controller][] = $action;
                }
            }
        }
    }

    private function u($controllerAction, $typeAcl, $role){
        foreach($controllerAction as $controller => $actionArray){
            foreach($actionArray as $action){
                //check isAllowed on each (controller, action)
                if($typeAcl->isAllowed($role, $controller, $action)){
                    $this->tempConfig[self::MAP_USER_SPECIAL][$role][$controller][] = $action;
                }
            }
        }
    }

    function arrayRecursiveDiff($aArray1, $aArray2){
        $aReturn = array();

        foreach($aArray1 as $mKey => $mValue){
            if(array_key_exists($mKey, $aArray2)){
                if(is_array($mValue)){
                    $aRecursiveDiff = $this->arrayRecursiveDiff($mValue, $aArray2[$mKey]);
                    if(count($aRecursiveDiff)){
                        $aReturn[$mKey] = $aRecursiveDiff;
                    }
                }else{
                    if($mValue != $aArray2[$mKey]){
                        $aReturn[$mKey] = $mValue;
                    }
                }
            }else{
                $aReturn[$mKey] = $mValue;
            }
        }
        return $aReturn;
    }

    public function getMap(){
        $this->tempConfig = array();
        foreach($this->tempConfig[self::MAP_ROLE_PARENT] as $role => $inheritRole){
            if(is_null($inheritRole)){
                $this->f($this->config[self::CONTROLLER_ACTION], $this->roleControllerActionAcl, $role);
            }
        }
        /*
         * map for child
         */
        foreach($this->tempConfig[self::MAP_ROLE_PARENT] as $role => $inheritRole){
            if(is_null($inheritRole)){
            }
            $arrayDiff = $this->config[self::CONTROLLER_ACTION];
            if(is_string($inheritRole)){
                $arrayDiff = $this->arrayRecursiveDiff($arrayDiff,
                    $this->tempConfig[self::MAP_ROLE_CONTROLLER_ACTION][$inheritRole]);
            }
            if(is_array($inheritRole)){
                foreach($inheritRole as $s_inheritRole){
                    $arrayDiff = $this->arrayRecursiveDiff($arrayDiff,
                        $this->tempConfig[self::MAP_ROLE_CONTROLLER_ACTION][$s_inheritRole]);
                }
            }
            //            var_dump($arrayDiff);
            $this->f($arrayDiff, $this->roleControllerActionAcl, $role);
        }
        //        $arrayDiff = $this->config[self::CONTROLLER_ACTION];
        //        $arrayDiff = $this->arrayRecursiveDiff($arrayDiff, $this->tempConfig[self::MAP_ROLE_CONTROLLER_ACTION]["guest"]);
        //        var_dump($arrayDiff);
        //        $this->f($arrayDiff, "editor");
        //        var_dump($this->tempConfig);
    }

    //    public function getWhereOnRole($role){
    //        $where = array();
    //        $this->buildConfig();
    //        var_dump($this->tempConfig);
    //        $where[$role] = $this->tempConfig[self::MAP_ROLE_CONTROLLER_ACTION][$role];
    //        $this->roleArray = array();
    //        $this->loopParent($role);
    //        foreach($this->roleArray as $parentRole){
    //            $where[$parentRole] = $this->tempConfig[self::MAP_ROLE_CONTROLLER_ACTION][$parentRole];
    //        }
    //
    //        return $where;
    //    }

    public function getAllOnRole($role){
        $user = [];
        $user["role"] = $role;
        $r = array();
        foreach($this->config[self::CONTROLLER_ACTION] as $controller => $actionArray){
            foreach($actionArray as $action){
                if($this->isUniAllowed($user, $controller, $action)){
                    $r[$controller][] = $action;
                }
            }
        }
        return $r;
    }

    //    public function getAllRoles(){
    //        return $this->roleControllerActionAcl->getRoles();
    //    }

    public function getAllControllerAction(){
        return $this->roleControllerActionAcl->getResources();
    }

    public function getRoleWhere(){
        $result = array();
        /** @warn $allRoles is used many time, save as global */
        $allRoles = $this->roleControllerActionAcl->getRoles();
        foreach($allRoles as $role){
            $inheritWhere = array();
            $notInheritWhere = array();
            foreach($this->config[self::CONTROLLER_ACTION] as $controller => $actionArray){
                foreach($actionArray as $action){
                    /**
                     * ROLE on CONTROLLER ACTION
                     */
                    if($this->roleControllerActionAcl->isAllowed($role, $controller, $action)){
                        $inheritWhere[$controller][] = $action;
                    }
                    /**
                     * ROLE on SPECIAL
                     */
                    if($this->roleSpecialAcl->hasRole($role)){
                        if($this->roleSpecialAcl->isAllowed($role, $controller, $action)){
                            $notInheritWhere[$controller][] = $action;
                        }
                    }
                }

            }
            $result[$role][self::INHERIT] = $inheritWhere;
            $result[$role][self::NOT_INHERIT] = $notInheritWhere;
        }
        return $result;
    }

    public function updateNewConfig($role, $newMapRoleControllerAction, $newMapRoleSpecial){
        $arrayDiff = array();
        /**
         * get controller action on role
         */
        //        $arrayDiff = array_diff($this->config[self::])
    }

    public function getControllerActionOnAclRole($role, $acl){
        $result = array();
        foreach($this->config[self::CONTROLLER_ACTION] as $controller => $actionArray){
            foreach($actionArray as $action){
                if($acl->isAllowed($role, $controller, $action)){
                    $result[$controller][] = $action;
                }
            }
        }
        return $result;
    }


}