<?php
namespace BackEnd\Service;

use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Resource\GenericResource;
use Zend\ServiceManager\ServiceManager;

class UniAcl extends Acl{
    /**@var ServiceManager $serviceManager */
    protected $serviceManager;

    /** @var  array */
    protected $uniAclConfig;

    /** @var  UniCache */
    protected $cache;


    /** @param ServiceManager $serviceManager */
    public function __construct($serviceManager){
        $this->serviceManager = $serviceManager;
        $this->cache = $serviceManager->get("UniCache");
        /**
         * if has cache, load from cache
         * if no cache, read from config
         *     then save to cache
         */
        $this->uniAclConfig = $this->cache->getArrayItem("UniAclConfig");
        if(!$this->uniAclConfig){
            $this->uniAclConfig = $this->serviceManager->get("config")["uniAcl"];
            /**
             * bcs, config>uniAcl may not store "resources"
             * read it from config, then add back to uniAcl
             */
            if(!isset($this->uniAclConfig["resources"])){
                $config = $this->serviceManager->get("config");
                //                $routes = $config["router"]["routes"];
                //                $controllerActionList = array();
                //                /**
                //                 * loop through routes, get (controller + action)
                //                 */
                //                foreach($routes as $routeName => $detail){
                //                    $defaults = $detail["options"]["defaults"];
                //                    $controllerActionList[] = $defaults["controller"] . "\\" . $defaults["action"];
                //                }
                $invokablesController = $config['controllers']['invokables'];
                $factoriesController = $config['controllers']['factories'];
                /** @var array $aclResources */
                $controllerArray = array_merge(array_keys($invokablesController), array_keys($factoriesController));
                //                $this->uniAclConfig["resources"] = $controllerActionList;
                $this->uniAclConfig["resources"] = $controllerArray;
            }
            /**
             * bcs cache NOT store UniAclConfig, add it
             */
            $this->cache->setArrayItem("UniAclConfig", $this->uniAclConfig);
        }
    }

    public function init(){
        /**
         * based on $uniAclConfig (from cache/app.config)
         * add roles
         * add resources
         * map role resources
         */
        $uniAclConfig = $this->uniAclConfig;
        /**
         * add roles
         */
        $roles = $uniAclConfig["roles"];
        foreach($roles as $role => $inherit){
            $this->addRole($role, $inherit);
        }
        /**
         * add resources
         */
        //        var_dump($this->uniAclConfig["resources"]);
        $this->addArrayResource($this->uniAclConfig["resources"]);
        /**
         * map role resource privilege
         */
        foreach($this->uniAclConfig["mapRoleResourcePrivilege"] as $role => $resourcePrivilege){
            foreach($resourcePrivilege as $resource => $privilege){
                $this->allow($role, $resource, $privilege);
            }
        }
        /**
         * map role special
         */
        //check directly as array(key) === value
        /**
         * map user special
         */
        //check directly as array(key) === value
    }

    /**
     * by default, addResource only allow ONE item at time
     * @param array $resources
     */
    public function addArrayResource($resources){
        foreach($resources as $resource){
            $this->addResource($resource);
        }
    }

    /**
     * resource.privilege on role
     * @param $role
     * @return array
     */
    public function getResourcePrivilegeOnRole($role){
        //        return $this->uniAclConfig["mapRoleResourcePrivilege"][$role];
        /**
         * return directly from config >>> no logic apply
         * editor can access which guest has, directly call has NO MEANING
         */
        //        foreach()
        return array();
    }

    /**
     * special on role
     * @param $role
     * @return array
     */
    public function getSpecialOnRole($role){
        $specialArray = array();
        foreach($this->uniAclConfig["mapRoleSpecial"][$role] as $special){
            $specialArray[] = $special;
        }
        return $specialArray;
    }

    /**
     * special on userId
     * @param $id
     * @return array
     */
    public function getSpecialOnUserId($id){
        $specialArray = array();
        foreach($this->uniAclConfig["mapUserIdSpecial"][$id] as $special){
            $specialArray[] = $special;
        }
        return $specialArray;
    }

    /**
     * check allow
     * @param null|string $id
     * @param string $controller
     * @param string $action
     * @return bool
     */
    public function isUniAllowed($id, $controller, $action){
        /**
         * find out WHICH user
         */
        //by default, $user is "guest"
        $userRole = "guest";
        if(!is_null($id)){
            //logined user may NOT has role
            if(isset($this->uniAclConfig["mapUserIdRole"][$id])){
                $userRole = $this->uniAclConfig["mapUserIdRole"][$id];
            }
        }
        /**
         * check by loop ROLE RESOURCE PRIVILEGE
         */
        if($this->isAllowed($userRole, $controller, $action)){
            return true;
        }
        /**
         * check by loop ROLE SPECIAL
         * check by array(key) === value
         */
        //remove foreach loop by isset @@, manh me vai
        if(isset($this->uniAclConfig["mapRoleSpecial"][$userRole])){
            //if this role has special, compare
            if(($controller . "\\" . $action) === $this->uniAclConfig["mapRoleSpecial"][$userRole]){
                return true;
            }
        }
        /**
         * check by loop USER SPECIAL
         */
        if(!is_null($id)){
            //check userId in list
            if(isset($this->uniAclConfig["mapUserIdSpecial"][$id])){
                //if in userId in list, compare with $controllerAction
                if($controller . "\\" . $action === $this->uniAclConfig["mapUserIdSpecial"][$id]){
                    return true;
                }
            }
        }
        return false;
    }
}