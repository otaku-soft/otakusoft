<?php

namespace classes\classBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * privateMessages
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class privateMessages
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
     * @ORM\Column(name="sendotakuid", type="integer")
     */
    public $sendotakuid;

     /**
     * @var integer
     *
     * @ORM\Column(name="tootakuid", type="integer")
     */
    public $tootakuid;
     /**
     * @var integer
     *
     * @ORM\Column(name="title", type="string",length = 255)
     */
    public $title;    
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
     /**
     * @var integer
     *
     * @ORM\Column(name="seen", type = "smallint")
     */
    public $seen;

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
