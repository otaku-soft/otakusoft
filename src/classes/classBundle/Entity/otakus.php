<?php

namespace classes\classBundle\Entity;
use FOS\UserBundle\Entity\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * otakus
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class otakus extends BaseUser
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
     * @ORM\Column(name="avatar", type="string", length=100)
     */
    public $avatar;
    /**
     * @var string
     *
     * @ORM\Column(name="aboutme", type="string", length=1000)
     */
    public $aboutme;
    /**
     * @var string
     *
     * @ORM\Column(name="hobbies", type="string", length=1000)
     */
    public $hobbies;
    /**
     * @var string
     *
     * @ORM\Column(name="favoriteAnimes", type="string", length=1000)
     */
    public $favoriteAnimes;
    /**
     * @var string
     *
     * @ORM\Column(name="favoriteGames", type="string", length=1000)
     */
    public $favoriteGames;
     /**
     * @var string
     *
     * @ORM\Column(name="nyanPoints", type="integer", length=100)
     */
    public $nyanPoints;   
    

    public function __construct()
    {
        $vars= array("avatar","aboutme","hobbies","favoriteAnimes","favoriteGames","nyanPoints");
        foreach ($vars as $key )
        {
            $this->$key = "";
        }
        parent::__construct();

    }

}
