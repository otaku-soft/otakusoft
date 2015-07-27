<?php

namespace classes\classBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * posts
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class posts
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
     * @ORM\Column(name="topicid", type="integer")
     */
    public $topicid;
     /**
     * @var integer
     *
     * @ORM\Column(name="otakuid", type="integer")
     */
    public $otakuid;  
     /**
     * @var integer
     *
     * @ORM\Column(name="message", type = "text")
     */
    public $message; 
     /**
     * @var integer
     *
     * @ORM\Column(name="dateCreated", type = "datetime")
     */
    public $dateCreated;
   
    public function __construct()
    {
        $class_vars = get_class_vars(get_class($this));
        foreach ($class_vars as $key => $value)
        {
            
            $this->$key = "";
        }
    }

}
