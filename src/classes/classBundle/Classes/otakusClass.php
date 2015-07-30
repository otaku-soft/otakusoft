<?php

namespace classes\classBundle\Classes;
use Doctrine\ORM\Mapping as ORM;

class otakusClass
{
    public $controller;
    public $user;
    public function __construct($controller)
    {
        $this->controller = $controller;
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
}
