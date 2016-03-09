<?php

namespace classes\classBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * subscriptions
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class subscriptions
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
     * @ORM\Column(name="topicid", type="integer")
     */
    public $topicid;
    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length = 45)
     */
    public $type;
    public function __construct()
    {
        $class_vars = get_class_vars(get_class($this));
        foreach ($class_vars as $key => $value)
        {
            
            $this->$key = "";
        }
    }

}
