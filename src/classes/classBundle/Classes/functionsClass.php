<?php

namespace classes\classBundle\Classes;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\Request;

class functionsClass
{
    public $controller;
    public function __construct($controller)
    {
        $this->controller = $controller;
    }
    public function copyRequestObject(&$object)
    {
        $request = Request::createFromGlobals();
        $request->getPathInfo();
        $arr =  $request->request->all();
        while (list($key, $value) = each($arr))
        {
            $object->$key = $value;
        }
    }
    public function navigationOffset($pagenumber)
    {
        return ($pagenumber - 1 ) * 10;
    }
    public function navigationTotalPages($count)
    {
        return (int)ceil($count / 10);
    }    

    public function getCountById($table,$parameters)
    {
        $connection = $this->controller->get('doctrine.dbal.default_connection');
        $parameterString = "";
        foreach ($parameters as $key => $value)
        {
            if ($parameterString != "")
            $parameterString = $parameterString ." AND ";
            $parameterString = $parameterString." ".$key."='".$value."'";
        }
        $sql = "SELECT  COUNT(*) AS  total FROM ".$table."  WHERE ".$parameterString;
        $countArray = $connection->executeQuery($sql)->fetch();
        return (int)$countArray['total'];;
    }
}