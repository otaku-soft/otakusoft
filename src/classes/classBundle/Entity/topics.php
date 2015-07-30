<?php

namespace classes\classBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * topics
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class topics
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public $id;
     /**
     * @var integer
     *
     * @ORM\Column(name="categoryid", type="integer")
     */
    public $categoryid;
     /**
     * @var integer
     *
     * @ORM\Column(name="forumid", type="integer")
     */
    public $forumid;
     /**
     * @var integer
     *
     * @ORM\Column(name="otakuid", type="integer")
     */
    public $otakuid;    
     /**
     * @var integer
     *
     * @ORM\Column(name="title", type="string",length = 255)
     */
    public $title;    
     /**
     * @var integer
     *
     * @ORM\Column(name="dateCreated", type = "datetime")
     */
    public $dateCreated; 
     /**
     * @var integer
     *
     * @ORM\Column(name="lastModified", type = "datetime")
     */
    public $lastModified;     


    public function __construct()
    {
        $class_vars = get_class_vars(get_class($this));
        foreach ($class_vars as $key => $value)
        {
            
            $this->$key = "";
        }
        $this->dateCreated = new \DateTime("now");
        $this->lastModified = new \DateTime("now");
    }

}
