<?php
namespace BackEnd\Model;

use Zend\ServiceManager\ServiceManager;

class UniAclConfigModel{
    /** @var  ServiceManager */
    protected $serviceManager;

    protected $uniAclConfig;

    protected $controllerActionArray;

    protected $cache;

    public function __construct($serviceManager){
        $this->serviceManager = $serviceManager;
        $this->cache = $this->serviceManager->get("UniCache");
        $uniAclConfig = $this->cache->getArrayItem("UniAclConfig");
        $this->uniAclConfig = $uniAclConfig;
    }

    /**
     * @difficult INHERIT >>> loop from child to get out for parent
     * mapRoleResourcePrivilege on show out
     * read from "roles" to view inherit
     * @param $role
     * @return array
     */
    public function getResourcePrivilegeOnRole($role){
        $roles = $this->uniAclConfig["roles"];
        $inherit = $role;
        $mapRoleResourcePrivilege = $this->uniAclConfig["mapRoleResourcePrivilege"];
        /**
         * reset first, may $this->controllerActionArray used by others
         * >>> array()
         */
        $this->controllerActionArray = array();
        /**
         * get controller action for itself
         */
        $this->controllerActionArray[$role] = $mapRoleResourcePrivilege[$role];
        /**
         * get child controller action
         * user loopInherit to
         * @WARN common fail on recursive
         * >>> borrow GLOBAL from field $this->controllerActionArray
         */
        $this->loopInherit($roles, $inherit, $mapRoleResourcePrivilege);

        return $this->controllerActionArray;
    }

    private function loopInherit($roles, $inherit, $mapRoleResourcePrivilege){
        $childInherit = $roles[$inherit];
        if(is_null($childInherit)){
            //get resource privilege from role
            $this->controllerActionArray[$inherit] = $mapRoleResourcePrivilege[$inherit];
            return;
        }
        if(is_string($childInherit)){
            //string means has this @@
            $this->controllerActionArray[$childInherit] = $mapRoleResourcePrivilege[$childInherit];
            //this role has SINGLE|STRING child
            //back to this recursive, call it
            $this->loopInherit($roles, $childInherit, $mapRoleResourcePrivilege);
        }
        if(is_array($childInherit)){
            //this role has MANY|ARRAY child
            foreach($childInherit as $childOfChildInherit){
                //back to this recursive, call it
                $this->loopInherit($roles, $childOfChildInherit, $mapRoleResourcePrivilege);
            }
        }
    }

    /**
     * get userRole from userId
     * @param $user
     * @return string
     */
    private function getUserRole($user){
        $userId = $user["id"];
        /**
         * @warn these "mapUserIdRole" NEED store in Unimedia as static/const
         */
        $userIdRole = $this->uniAclConfig["mapUserIdRole"];
        $userRole = "guest";
        /** @warn this check TOO DEPENDENCY
         * i need an warn on $userId when get
         */
        if(!is_null($userId)){
            if(isset($userIdRole[$userId])){
                //directly access to $userId may FAIL
                //bcs logined user may NOT acl
                $userRole = $userIdRole[$userId];
            }
        }
        return $userRole;
    }

    /**
     * get special on role
     */
    public function getSpecialOnRole($role){
        $roleSpecial = array();
        $mapRoleSpecial = $this->uniAclConfig["mapRoleSpecial"];
        if(isset($mapRoleSpecial[$role])){
            $roleSpecial = $mapRoleSpecial[$role];
        }
        return $roleSpecial;
    }

    public function getSpecialOnUser($userId){
        $userIdSpecial = array();
        $mapUserIdSpecial = $this->uniAclConfig["mapUserIdSpecial"];
        if(isset($mapUserIdSpecial[$userId])){
            $userIdSpecial = $mapUserIdSpecial[$userId];
        }
        return $userIdSpecial;
    }


    public function getControllerActionOnUser(){
        $userModel = new UserModel($this->serviceManager);
        $user = $userModel->getUser();
        $userRole = $this->getUserRole($user);
        $ar = array();
        $ar["RoleControllerAction"] = $this->getResourcePrivilegeOnRole($userRole);
        $ar["RoleSpecial"] = $this->getSpecialOnRole($userRole);
        $ar["UserIdSpecial"] = $this->getSpecialOnUser($user["id"]);
        return $ar;
    }
}