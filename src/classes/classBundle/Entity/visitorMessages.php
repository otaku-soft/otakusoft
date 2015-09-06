<?php

namespace classes\classBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
/**
 *  visitorMessages
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class visitorMessages
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
     * @ORM\Column(name="otakuid", type="integer")
     */
    public $otakuid ;
         /**
     * @var integer
     *
     * @ORM\Column(name="friendotakuid", type="integer")
     */
    public $friendotakuid ;
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
        $this->dateCreated = new \DateTime("now");

    }

}
