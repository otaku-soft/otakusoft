<?php
namespace classes\classBundle\Classes;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use classes\classBundle\Classes\functionsClass;
use Doctrine\ORM\Mapping as ORM;
class otakusClass
{
    public $container;
    public $user;
    public $functionsClass;
    public function __construct(ContainerInterface $container,EntityManager $em)
    {
        $this->container = $container;
        $this->em = $em;
        $this->connection = $this->container->get('doctrine.dbal.default_connection');
        $this->functionsClass = $this->container->get("functionsClass");
        $this->user = $this->container->get('security.context')->getToken()->getUser();

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
        $repository = $this->em->getRepository('classesclassBundle:otakus');
        $user =  $repository->findOneBy(array("id" => $this->getId()));
        return $user;
    }
    function incrementNyanPoints($points)
    {
        $user = $this->getDoctrineUser();
        $user->nyanPoints = $user->nyanPoints + $points;
        $this->em->flush();
    }
    function getSqlUser()
    {
        $sql = "SELECT * FROM otakus WHERE id =".$this->getId();
        return $this->functionsClass->arrayToObject($this->connection->executeQuery($sql)->fetch());
    }
}
