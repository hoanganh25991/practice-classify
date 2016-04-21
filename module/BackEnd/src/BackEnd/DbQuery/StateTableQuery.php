<?php
namespace BackEnd\DbQuery;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\ArrayUtils;

class StateTableQuery{
    protected $dbAdapter;
    protected $table = "state";

    public function __construct(ServiceManager $serviceManager){
        $config = $serviceManager->get('config');
        $adpater = new Adapter($config['db']);
        $this->dbAdapter = $adpater;
    }

    /**
     * @param array $condition
     * @return array
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
            $firstResultSet = $resultSet[0];
        }
        return $firstResultSet;
    }

    public function save(array $state){
        $record = $this->findWhere([$state['name']]);
        if($record){
            //update
        }else{
            //insert
        }

    }
}