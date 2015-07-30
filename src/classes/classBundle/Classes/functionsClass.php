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
}