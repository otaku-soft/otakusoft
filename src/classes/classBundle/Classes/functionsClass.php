<?php

namespace classes\classBundle\Classes;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\Request;

class functionsClass
{
    public $controller;
    public $db;
    public function __construct($controller)
    {
        $this->controller = $controller;
        $this->db = mysqli_connect("localhost","root","localpass","otakusoft") or die("Error " . mysqli_error($link)); 
    }
    public function escapeString($string)
    {
      return   trim(mysqli_real_escape_string ( $this->db , $string ));
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
        return $this->escapeString(($pagenumber - 1 ) * 10);
    }
    public function navigationTotalPages($count)
    {
        return $this->escapeString((int)ceil($count / 10));
    }    

    public function getCountById($table,$parameters = array())
    {
        $connection = $this->controller->get('doctrine.dbal.default_connection');
        $parameterString = "";
        foreach ($parameters as $key => $value)
        {
            if ($parameterString != "")
            $parameterString = $parameterString ." AND ";
            $parameterString = $parameterString." ".$key."='".$this->escapeString($value)."'";
        }
        if ($parameterString != "")
        $parameterString  =  "  WHERE ".$parameterString;
        $sql = "SELECT  COUNT(*) AS  total FROM ".$table.$parameterString;
        $countArray = $connection->executeQuery($sql)->fetch();
        return (int)$countArray['total'];;
    }

    public function addfield(&$fields,$name,$description,$options = array())
    {
        $fields[$name] = new \stdClass();
        $fields[$name]->name = $name;
        $fields[$name]->description = $description;
        $fields[$name]->options = $options;
    }

    function arrayToObject($array)
    {
        // First we convert the array to a json string
        $json = json_encode($array);
        // The we convert the json string to a stdClass()
        $object = json_decode($json);
        return $object;
    }

    function doubleArrayToObject($array)
    {
        $array2 = array();
        foreach ($array as $item)
        {
            $array2[] = $this->arrayToObject($item);
        }
        return $array2;
    }
}