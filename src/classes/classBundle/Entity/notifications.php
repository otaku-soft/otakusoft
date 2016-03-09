<?php

namespace classes\classBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * notifications
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class notifications
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
     * @ORM\Column(name="type", type="string", length = 45)
     */
    public $type;
    /**
     * @var string
     *
     * @ORM\Column(name="postid", type="integer")
     */
    public $postid;
    /**
     * @var string
     *
     * @ORM\Column(name="friendRequestSendOtakuid", type="integer")
     */
    public $friendRequestSendOtakuid;
    /**
     * @var string
     *
     * @ORM\Column(name="friendRequestAcceptedOtakuid", type="integer")
     */
    public $friendRequestAcceptedOtakuid;
    /**
     * @var string
     *
     * @ORM\Column(name="visitormessageid", type="integer")
     */
    public $visitormessageid;
    /**
     * @var string
     *
     * @ORM\Column(name="seen", type="smallint")
     */
    public $seen;


    
    public function __construct()
    {
        $class_vars = get_class_vars(get_class($this));
        foreach ($class_vars as $key => $value)
        {
            
            $this->$key = "";
        }
    }

}
