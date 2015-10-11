<?php

namespace classes\classBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * contentCategories
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class contentCategories
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
     * @ORM\Column(name="title", type="string",length = 255)
     */
    public $title;    

    public function __construct()
    {
        $class_vars = get_class_vars(get_class($this));
        foreach ($class_vars as $key => $value)
        {
            
            $this->$key = "";
        }
    }

}
