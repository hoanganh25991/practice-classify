<?php
namespace BackEnd\Service;

use Zend\Cache\Storage\Adapter\Filesystem;

class UniCache extends Filesystem{
    /**
     * @var string $key
     * @var array $array
     */
    public function setArrayItem($key, $array){
        $serializedArray = serialize($array);
        $this->setItem($key, $serializedArray);
    }

    /**@var string $key
     * @return array
     */
    public function getArrayItem($key){
        $serializedArray = $this->getItem($key);
        return unserialize($serializedArray);
    }
}