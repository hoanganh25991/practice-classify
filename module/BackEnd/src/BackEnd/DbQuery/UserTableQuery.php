<?php
namespace BackEnd\DbQuery;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\ArrayUtils;

class UserTableQuery{
    protected $dbAdapter;
    protected $table = "user";

    public function __construct(ServiceManager $serviceManager){
        $config = $serviceManager->get('config');
        $adpater = new Adapter($config['db']);
        $this->dbAdapter = $adpater;
    }

    /**
     * @param array $condition
     * @return array $firstResultSet
     */
    public function findWhere(array $condition){
        $sql = new Sql($this->dbAdapter);
        $select = $sql->select();

        $select->from($this->table)->where($condition);

        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        $resultSet = ArrayUtils::iteratorToArray($result);
        $firstResultSet = [];
        if($resultSet){
            var_dump("has user");
            $firstResultSet = $resultSet[0];
        }
        return $firstResultSet;
    }
}