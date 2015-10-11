<?php

namespace classes\classBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * contentSubSections
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class contentSubSections
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
     * @ORM\Column(name="contentCategoryid", type="integer")
     */
    public $contentCategoryid;
     /**
     * @var integer
     *
     * @ORM\Column(name="contentSectionid", type="integer")
     */
    public $contentSectionid;
     /**
     * @var integer
     *
     * @ORM\Column(name="title", type="string",length = 255)
     */
    public $title;    
     /**
     * @var integer
     *
     * @ORM\Column(name="description", type = "text")
     */
    public $description; 

    public function __construct()
    {
        $class_vars = get_class_vars(get_class($this));
        foreach ($class_vars as $key => $value)
        {
            
            $this->$key = "";
        }
    }

}
