<?php

namespace FunPro\FinancialBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FunPro\PassengerBundle\Entity\Passenger;
use JMS\Serializer\Annotation as JS;

/**
 * FavoriteDiscountCodes
 *
 * @ORM\Table(
 *     name="favorite_discount_codes",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="unique_code_per_user", columns={"passenger_id", "discount_code_id"})}
 * )
 *
 * @ORM\Entity(repositoryClass="FunPro\FinancialBundle\Repository\FavoriteDiscountCodesRepository")
 */
class FavoriteDiscountCodes
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @JS\Since("1.0.0")
     * @JS\Groups({"Admin"})
     */
    private $id;

    /**
     * @var Passenger
     * 
     * @ORM\ManyToOne(targetEntity="FunPro\PassengerBundle\Entity\Passenger")
     * @ORM\JoinColumn(name="passenger_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     *
     * @JS\Since("1.0.0")
     * @JS\Groups({"Admin"})
     */
    private $passenger;

    /**
     * @var DiscountCode
     * 
     * @ORM\ManyToOne(targetEntity="FunPro\FinancialBundle\Entity\DiscountCode")
     * @ORM\JoinColumn(name="discount_code_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     *
     * @JS\Since("1.0.0")
     * @JS\Groups({"Admin", "Passenger"})
     * @JS\MaxDepth(2)
     */
    private $discountCode;

    /**
     * @var boolean
     * 
     * @ORM\Column(type="boolean")
     *
     * @JS\Since("1.0.0")
     * @JS\Groups({"Admin", "Passenger"})
     */
    private $active;

    /**
     * FavoriteDiscountCodes constructor.
     * @param Passenger $passenger
     * @param DiscountCode $discountCode
     */
    public function __construct(Passenger $passenger, DiscountCode $discountCode)
    {
        $this->passenger = $passenger;
        $this->discountCode = $discountCode;
        $this->setActive(false);
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Passenger
     */
    public function getPassenger()
    {
        return $this->passenger;
    }

    /**
     * @param Passenger $passenger
     * @return FavoriteDiscountCodes;
     */
    public function setPassenger($passenger)
    {
        $this->passenger = $passenger;
        return $this;
    }

    /**
     * @return DiscountCode
     */
    public function getDiscountCode()
    {
        return $this->discountCode;
    }

    /**
     * @param DiscountCode $discountCode
     * @return FavoriteDiscountCodes;
     */
    public function setDiscountCode($discountCode)
    {
        $this->discountCode = $discountCode;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param boolean $active
     * @return FavoriteDiscountCodes;
     */
    public function setActive($active)
    {
        $this->active = $active;
        return $this;
    }
}
