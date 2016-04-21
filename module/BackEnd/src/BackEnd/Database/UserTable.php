<?php
namespace BackEnd\Database;

use BackEnd\Service\Encrypt;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\ArrayUtils;

class UserTable{
    protected $dbAdapter;

    const TABLE = "user";

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
    public function findWhere(array $condition){
        $sql = new Sql($this->dbAdapter);
        $select = $sql->select();

        $select->from(self::TABLE)->where($condition);

        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        /** @var array $resultArray */
        $resultArray = ArrayUtils::iteratorToArray($result);
        $user = false;
        if(count($resultArray) > 0){
            $user = $resultArray[0];
        }
        return $user;
    }


    public function insert(array $user){
        /*
         * check user before insert
         */

        $result = $this->findWhere(array("email" => $user["email"]));

        if(!$result){
            $hashedPass = Encrypt::hash($user["password"]);
            $user["password"] = $hashedPass;

            $columns = array_keys($user);
            $values = array_values($user);
            $insert = $this->sql->insert();
            $insert->columns($columns)->values($values);
            $statement = $this->sql->prepareStatementForSqlObject($insert);
            $statement->execute();
            /* @warn true false what it is means */
            return true;
        }
        return false;
    }
}