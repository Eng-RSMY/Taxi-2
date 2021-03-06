<?php

namespace FunPro\PassengerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FunPro\UserBundle\Entity\User;
use FunPro\UserBundle\Interfaces\SMSInterface;
use JMS\Serializer\Annotation as JS;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Passenger
 *
 * @ORM\Table(name="passenger")
 * @ORM\Entity(repositoryClass="FunPro\PassengerBundle\Repository\PassengerRepository")
 *
 * @UniqueEntity("mobile", groups={"Register"})
 */
class Passenger extends User implements SMSInterface
{
    /**
     * @var string
     *
     * @ORM\Column(name="mobile", type="string", length=11, unique=true)
     *
     * @Assert\NotBlank(groups={"Register", "Profile"})
     * @Assert\Regex(pattern="/09\d{9}/", groups={"Register", "Profile"})
     *
     * @JS\Groups({"PassengerMobile", "Profile", "Admin"})
     * @JS\Since("1.0.0")
     */
    protected $mobile;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="FunPro\PassengerBundle\Entity\Passenger")
     * @ORM\JoinColumn(name="referer_id", referencedColumnName="id", onDelete="Restrict")
     *
     * @Assert\Type(type="FunPro\PassengerBundle\Entity\Passenger", groups={"Register", "Profile"})
     *
     * @JS\Groups({"Referrer", "Profile", "Admin"})
     * @JS\MaxDepth(1)
     * @JS\Since("1.0.0")
     */
    private $referrer;

    /**
     * @var float
     *
     * @ORM\Column(name="rate", type="decimal", precision=2, scale=1, options={"default"=0})
     *
     * @JS\Groups({"Public", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $rate;

    /**
     * @var integer
     *
     * @ORM\Column(name="wrong_token_count", type="smallint")
     *
     * @JS\Exclude()
     */
    private $wrongTokenCount;

    public function __construct()
    {
        parent::__construct();
        $this->setEnabled(true);
        $this->addRole(self::ROLE_PASSENGER);
        $this->rate = 0;
        $this->setWrongTokenCount(0);
    }

    /**
     * Get mobile
     *
     * @return string
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * Set mobile
     *
     * @param string $mobile
     *
     * @return Passenger
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;
        $this->setUsername($mobile);

        return $this;
    }

    /**
     * Get rate
     *
     * @return string
     */
    public function getRate()
    {
        return $this->rate;
    }

    /**
     * Set rate
     *
     * @param string $rate
     *
     * @return Passenger
     */
    public function setRate($rate)
    {
        $this->rate = $rate;

        return $this;
    }

    /**
     * Get referrer
     *
     * @return Passenger
     */
    public function getReferrer()
    {
        return $this->referrer;
    }

    /**
     * Set referrer
     *
     * @param Passenger $referrer
     *
     * @return Passenger
     */
    public function setReferrer(Passenger $referrer = null)
    {
        $this->referrer = $referrer;

        return $this;
    }

    /**
     * @return int
     */
    public function getWrongTokenCount()
    {
        return $this->wrongTokenCount;
    }

    /**
     * Set wrongTokenCount
     *
     * @param integer $wrongTokenCount
     *
     * @return Passenger
     */
    public function setWrongTokenCount($wrongTokenCount)
    {
        $this->wrongTokenCount = $wrongTokenCount;

        return $this;
    }

    /**
     * increase wrong token count
     */
    public function increaseWrongTokenCount()
    {
        ++$this->wrongTokenCount;
    }

    public function resetWrongTokenCount()
    {
        $this->wrongTokenCount = 0;
    }
}
