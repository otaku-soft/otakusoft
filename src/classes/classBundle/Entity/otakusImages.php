<?php

namespace classes\classBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * otakusImages
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class otakusImages
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
    public $otakuid;
     /**
     * @var integer
     *
     * @ORM\Column(name="name", type="text")
     */
    public $name;  

     /**
     * @var integer
     *
     * @ORM\Column(name="filename", type="text")
     */
    public $filename;    
     /**
     * @var integer
     *
     * @ORM\Column(name="dateuploaded", type = "datetime")
     */
    public $dateuploaded; 
    

    public function __construct()
    {
        $class_vars = get_class_vars(get_class($this));
        foreach ($class_vars as $key => $value)
        {
            if ($key != "dateuploaded")
            $this->$key = "";
        }
    }

}
