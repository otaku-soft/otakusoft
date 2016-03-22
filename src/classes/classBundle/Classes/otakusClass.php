<?php

namespace classes\classBundle\Classes;
use classes\classBundle\Classes\functionsClass;
use Doctrine\ORM\Mapping as ORM;

class otakusClass
{
    public $controller;
    public $user;
    public $functionsClass;
    public function __construct($controller)
    {
        $this->controller = $controller;
        $this->connection = $this->controller->get('doctrine.dbal.default_connection');
        $this->functionsClass = new functionsClass($controller);
        $this->user = $this->controller->get('security.context')->getToken()->getUser();

    }
    public function getId()
    {
        $user =  $this->user;
        if (is_object($user))
        {
            return $this->user->getId();
        }
        return -1;
    }
    public function isRole($string)
    {
        $user = $this->user;
        if (is_object($user))
        {
            $roles = $user->getRoles();
            foreach ($roles as $role)
            {
                if ($role == $string)
                return true;
            }
        } 
        return false;       
    }
    function getPostCount($id)
    {
        return $this->functionsClass->getCountById("topics",array("otakuid" => $id))  + $this->functionsClass->getCountById("posts",array("otakuid" => $id));
    }
    function getDoctrineUser()
    {
        $em = $this->controller->getDoctrine()->getManager();
        $repository = $em->getRepository('classesclassBundle:otakus');
        $user =  $repository->findOneBy(array("id" => $this->getId()));
        return $user;
    }
    function incrementNyanPoints($points)
    {
        $em = $this->controller->getDoctrine()->getManager();
        $user = $this->getDoctrineUser();
        $user->nyanPoints = $user->nyanPoints + $points;
        $em->flush();
    }
    function getSqlUser()
    {
        $sql = "SELECT * FROM otakus WHERE id =".$this->getId();
        return $this->functionsClass->arrayToObject($this->connection->executeQuery($sql)->fetch());
    }
}
