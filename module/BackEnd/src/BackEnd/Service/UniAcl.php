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
    const MAP_ROLE_PARENT = "MAP_ROLE_PARENT";
    const CONTROLLER_ACTION = "CONTROLLER_ACTION";
    const ACTION = "ACTION";
    const SPECIAL = "SPECIAL";
    const MAP_ROLE_CONTROLLER_ACTION = "MAP_ROLE_CONTROLLER_ACTION";
    const MAP_ROLE_SPECIAL = "MAP_ROLE_SPECIAL";
    const MAP_USER_SPECIAL = "MAP_USER_SPECIAL";
    const ROLE_CONTROLLER_ACTION = "ROLE_CONTROLLER_ACTION";
    const ROLE_SPECIAL = "ROLE_SPECIAL";
    const USER_SPECIAL = "USER_SPECIAL";

    const ROLE = "ROLE";
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
        $this->addResource($this->roleControllerActionAcl);

        //3. map ROLE CONTROLLER ACTION
        if(isset($this->config[self::MAP_ROLE_CONTROLLER_ACTION])){
            $this->allow($this->config[self::MAP_ROLE_CONTROLLER_ACTION], $this->roleControllerActionAcl);
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
        $this->addResource($this->roleSpecialAcl);

        //3. map ROLE SPECIAL
        if(isset($this->config[self::MAP_ROLE_SPECIAL])){
            $this->allow($this->config[self::MAP_ROLE_SPECIAL], $this->roleSpecialAcl);
        }

        /**
         * USER SPECIAL
         * 1. add ROLE
         * 2. add CONTROLLER
         * 3. map USER SPECIAL
         */
        //1. add ROLE
        //add role base on map
        //each user with his id as role, allow userSpecialAcl open permission
        //from his id to "where"
        if(isset($this->config[self::MAP_USER_SPECIAL])){
            foreach($this->config[self::MAP_USER_SPECIAL] as $role => $controllerAction){
                $this->userSpecialAcl->addRole($role);
            }
        }
        //2. add CONTROLLER
        //note: USER SPECIAL share same CONTROLLER
        $this->addResource($this->userSpecialAcl);
        //3. map USER CONTROLLER
        if(isset($this->config[self::MAP_USER_SPECIAL])){
            $this->allow($this->config[self::MAP_USER_SPECIAL], $this->userSpecialAcl);
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
     * 3 acl in UniAcl share the same controller-action resource
     * @param Acl $acl
     */
    private function addResource($acl){
        $controllerArray = array_keys($this->config[self::CONTROLLER_ACTION]);
        foreach($controllerArray as $controller){
            $acl->addResource($controller);
        }
    }

    /**
     * acl allow role-"where"
     * @param array $mapRoleControllerAction
     * @param Acl $acl
     */
    private function allow($mapRoleControllerAction, $acl){
        foreach($mapRoleControllerAction as $role => $controllerAction){
            foreach($controllerAction as $controller => $actionArray){
                $acl->allow($role, $controller, $actionArray);
            }
        }
    }

    /**
     * all roles for
     * ROLE CONTROLLER ACTION
     * ROLE SPECIAL
     * bcs role controller action is lager
     * get from it
     * @return array $allRoles
     */
    public function getAllRoles(){
        return $this->roleControllerActionAcl->getRoles();
    }

    /**
     * get where on role at which acl
     */
    /**
     * @param string $role
     * @param Acl $acl
     * @return array $result
     */
    public function getWhereOnRoleAtAcl($role, $acl){
        $result = array();
        foreach($this->cA as $controller => $actionArray){
            foreach($actionArray as $action){
                if($acl->isAllowed($role, $controller, $action)){
                    $result[$controller][] = $action;
                }
            }
        }
        return $result;
    }

    /**
     * @param $newConfig
     * @param $action
     */
    public function update($newConfig, $action){
        $role = $newConfig[self::ROLE];
        if($action === "deny"){
            /**
             * update map ROLE CONTROLLER ACTION
             */
            $oldConfig = $this->getWhereOnRoleAtAcl($role, $this->roleControllerActionAcl);
            $whereDeny = $this->arrayRecursiveDiff($oldConfig, $newConfig[self::INHERIT]);
            foreach($whereDeny as $controller => $actionArray){
                foreach($actionArray as $action){
                    $this->uniDeny($role, $controller, $action, $this->roleControllerActionAcl);
                }
            }
            /**
             * update map ROLE SPECIAL
             */
            $oldConfig = $this->getWhereOnRoleAtAcl($role, $this->roleSpecialAcl);
            $whereDeny = $this->arrayRecursiveDiff($oldConfig, $newConfig[self::NOT_INHERIT]);
            foreach($whereDeny as $controller => $actionArray){
                foreach($actionArray as $action){
                    $this->uniDeny($role, $controller, $action, $this->roleSpecialAcl);
                }
            }
        }
        if($action === "allow"){
            /**
             * update for role controller action
             */
            $role = $newConfig[self::ROLE];
            foreach($newConfig[self::INHERIT] as $controller => $actionArray){
                $this->roleControllerActionAcl->allow($role, $controller, $actionArray);
            }
            /**
             * update for role special
             */
            foreach($newConfig[self::NOT_INHERIT] as $controller => $actionArray){
                $this->roleSpecialAcl->allow($role, $controller, $actionArray);
            }
        }
    }

    public function setConfig($config){
        $this->config = $config;
    }

    public function getConfig(){
        return $this->config;
    }

    /**
     * @param $role
     * @param $controller
     * @param $action
     * @param Acl $acl
     */
    private function uniDeny($role, $controller, $action, $acl){
        if($acl->isAllowed($role, $controller, $action)){
            $acl->deny($role, $controller, $action);
            /**
             * editor deny from roleA >>> guest also deny from roleA
             */
            $this->roleArray = array();
            $this->loopParent($role);
            foreach($this->roleArray as $parentRole){
                $acl->deny($parentRole, $controller, $action);
            }

            /**
             * editor deny from role A >>> admin NOT deny from
             */
            $this->roleArray = array();
            $this->loopInherit($role);
            foreach($this->roleArray as $inheritRole){
                $acl->allow($inheritRole, $controller, $action);
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
                if($this->userSpecialAcl->isAllowed($role, $controller, $action)){
                    return true;
                }
            }
        }
        return false;
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
                $this->tempConfig[self::MAP_ROLE_CONTROLLER_ACTION][$role] =
                    $this->getWhereOnRoleAtAcl($role, $this->roleControllerActionAcl);
            }
        }
        /*
         * map for child
         */
        foreach($this->tempConfig[self::MAP_ROLE_PARENT] as $role => $inheritRole){
            $arrayDiff = $this->config[self::CONTROLLER_ACTION];
            if(is_array($inheritRole)){
                foreach($inheritRole as $s_inheritRole){
                    $arrayDiff = $this->arrayRecursiveDiff($arrayDiff,
                        $this->tempConfig[self::MAP_ROLE_CONTROLLER_ACTION][$s_inheritRole]);
                }
            }
            $result = $this->filterArrayByIsAllowedOnRole($arrayDiff, $role, $this->roleControllerActionAcl);
            $this->tempConfig[self::MAP_ROLE_CONTROLLER_ACTION][$role] = $result;
        }
        /**
         * map ROLE SPECIAL
         */
        //        $allRoles = $this->roleControllerActionAcl->getRoles();
        foreach($this->getAllRoles() as $role){
            //            if($this->roleSpecialAcl->hasRole($role)){
            $result = $this->filterArrayByIsAllowedOnRole($this->cA, $role, $this->roleSpecialAcl);
            $this->tempConfig[self::MAP_ROLE_SPECIAL][$role] = $result;
            //            }
        }

        /**
         * map USER SPECIAL
         */
        $allRoles = $this->userSpecialAcl->getRoles();
        foreach($allRoles as $role){
            $result = $this->filterArrayByIsAllowedOnRole($this->cA, $role, $this->userSpecialAcl);
            $this->tempConfig[self::MAP_USER_SPECIAL][$role] = $result;
        }
        //        $this->config = $this->tempConfig;
        return $this->tempConfig;
    }

    /**
     * @param $controllerAction
     * @param $role
     * @param Acl $acl
     * @return array
     */
    private function filterArrayByIsAllowedOnRole($controllerAction, $role, $acl){
        $result = array();
        foreach($controllerAction as $controller => $actionArray){
            foreach($actionArray as $action){
                if($acl->isAllowed($role, $controller, $action)){
                    $result[$controller][] = $action;
                }
            }
        }
        return $result;
    }

    private function arrayRecursiveDiff($aArray1, $aArray2){
        $aReturn = array();

        foreach($aArray1 as $mKey => $mValue){
            if(array_key_exists($mKey, $aArray2)){
                if(is_array($mValue)){
                    $temp = $aArray2[$mKey];
                    $aRecursiveDiff = $this->arrayRecursiveDiff($mValue, $temp);
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
            $result[self::MAP_ROLE_CONTROLLER_ACTION][$role] = $inheritWhere;
            $result[self::MAP_ROLE_SPECIAL][$role] = $notInheritWhere;
        }
        return $result;
    }

    public function getConfigForUI(){
        $result = $this->config;
        $mapRoleWhere = $this->getRoleWhere();
        $result[self::MAP_ROLE_CONTROLLER_ACTION] = $mapRoleWhere[self::MAP_ROLE_CONTROLLER_ACTION];
        $result[self::MAP_ROLE_SPECIAL] = $mapRoleWhere[self::MAP_ROLE_SPECIAL];
        return $result;
    }
}