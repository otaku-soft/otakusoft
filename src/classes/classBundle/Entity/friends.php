<?php

namespace classes\classBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * friends
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class friends
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
     * @var string
     *
     * @ORM\Column(name="otakuid", type="integer")
     */
    public $otakuid;
    /**
     * @var string
     *
     * @ORM\Column(name="friendotakuid", type="integer")
     */
    public $friendotakuid;
    /**
     * @var string
     *
     * @ORM\Column(name="status", type="integer")
     */
    public $status;

    
    public function __construct()
    {
        $class_vars = get_class_vars(get_class($this));
        foreach ($class_vars as $key => $value)
        {
            
            $this->$key = "";
        }
    }

}
