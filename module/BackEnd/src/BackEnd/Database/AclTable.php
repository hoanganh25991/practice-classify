<?php
namespace BackEnd\Database;

use BackEnd\Service\Encrypt;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\ArrayUtils;

class AclTable{
    protected $dbAdapter;

    const TABLE = "acl";

    protected $sql;


    public function __construct(ServiceManager $serviceManager){
        $config = $serviceManager->get('config');
        $adpater = new Adapter($config['db']);
        $this->dbAdapter = $adpater;
        /** @warn sql many talk too many table,
         * usertable ONLY handle user many not enough */
        $this->sql = new Sql($adpater, self::TABLE);
    }

    /**
     * @param array $condition
     * @return array|false $firstResultSet
     */
    public function getLastRow(){
        //SELECT fields FROM table ORDER BY id DESC LIMIT 1
        $sql = new Sql($this->dbAdapter);
        $select = $sql->select();

        $select->columns(array("*"))->from(self::TABLE)->order('id DESC')->limit(1);

        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        /** @var array $resultArray */
        $resultArray = ArrayUtils::iteratorToArray($result);
        $aclConfig = array();
        if(count($resultArray) > 0){
            $aclConfig = $resultArray[0];
        }
        return $aclConfig;
    }


    public function insert($config){
        $serializedConfig = serialize($config);
        $insert = $this->sql->insert();
        $insert->columns(array("config"))->values(array($serializedConfig));
        $statement = $this->sql->prepareStatementForSqlObject($insert);
        $statement->execute();
        /* @warn true false what it is means */
        //        return true;
    }
}