<?php

namespace FunPro\UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JS;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="FunPro\UserBundle\Repository\UserRepository")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="disc", type="smallint")
 * @ORM\DiscriminatorMap({
 *      0 = "FunPro\UserBundle\Entity\User",
 *      User::TYPE_ADMIN = "FunPro\UserBundle\Entity\User",
 *      User::TYPE_OPERATOR = "FunPro\UserBundle\Entity\Operator",
 *      User::TYPE_DRIVER = "FunPro\DriverBundle\Entity\Driver",
 *      User::TYPE_PASSENGER = "FunPro\PassengerBundle\Entity\Passenger",
 * })
 * @ORM\AttributeOverrides({
 *      @ORM\AttributeOverride(
 *          name="emailCanonical",
 *          column=@ORM\Column(
 *              name="email_canonical",
 *              type="string",
 *              length=255,
 *              nullable=true,
 *              unique=true
 *          )
 *      ),
 *      @ORM\AttributeOverride(
 *          name="email",
 *          column=@ORM\Column(
 *              name="email",
 *              nullable=true,
 *          )
 *      ),
 *      @ORM\AttributeOverride(
 *          name="usernameCanonical",
 *          column=@ORM\Column(
 *              name="username_canonical",
 *              type="string",
 *              length=255,
 *              nullable=true,
 *              unique=true
 *          )
 *      ),
 *      @ORM\AttributeOverride(
 *          name="username",
 *          column=@ORM\Column(
 *              name="username",
 *              nullable=true,
 *          )
 *      )
 * })
 *
 * ### Username s are nullable, because prevent spam passenger username ###
 *
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=true)
 *
 * @Vich\Uploadable()
 */
class User extends BaseUser
{
    const SEX_MALE = 0;
    const SEX_FEMALE = 1;

    const TYPE_ADMIN = 1;
    const TYPE_OPERATOR = 2;
    const TYPE_MARKETER = 3;
    const TYPE_AGENCY_ADMIN = 4;
    const TYPE_DRIVER = 5;
    const TYPE_PASSENGER = 6;

    const ROLE_ADMIN = 'ROLE_ADMIN';
    const ROLE_OPERATOR = 'ROLE_OPERATOR';
    const ROLE_MARKETER = 'ROLE_MARKETER';
    const ROLE_AGENCY_ADMIN = 'ROLE_AGENCY_ADMIN';
    const ROLE_DRIVER = 'ROLE_DRIVER';
    const ROLE_PASSENGER = 'ROLE_PASSENGER';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @JS\Groups({"Public", "Admin"})
     * @JS\Since("1.0.0")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=50, nullable=true)
     *
     * @Assert\Regex(pattern="/\d/", match=false, groups={"Register", "Profile"})
     *
     * @JS\Groups({"Public", "Register", "Profile", "Admin"})
     * @JS\Since("1.0.0")
     */
    protected $name;

    /**
     * @deprecated
     * @var integer
     *
     * @ORM\Column(type="smallint", nullable=true)
     *
     * @Assert\Type(type="numeric", groups={"Register", "Profile"})
     * @Assert\Range(min="13", max="99", groups={"Register", "Profile"})
     *
     * @JS\Groups({"Owner", "Register", "Profile", "Admin"})
     * @JS\Since("1.0.0")
     */
    protected $age;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="born", type="date", nullable=true, options={"default"="2017-01-01"})
     *
     * @Assert\Date(groups={"Register", "Update"})
     *
     * @JS\Groups({"Owner", "Register", "Profile", "Admin"})
     * @JS\Since("1.0.0")
     */
    protected $born;

    /**
     * @var integer
     *
     * @ORM\Column(type="string", length=1, nullable=true)
     *
     * @Assert\Regex(pattern="/[01]/", groups={"Register", "Profile"})
     *
     * @JS\Groups({"Public", "Profile", "Admin"})
     * @JS\Since("1.0.0")
     */
    protected $sex;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @Assert\Length(max="2000", groups={"Register", "Profile"})
     *
     * @JS\Groups({"Public", "Profile", "Admin"})
     * @JS\Since("1.0.0")
     */
    protected $description;

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     *
     * @JS\Groups({"Public", "Profile", "Admin"})
     * @JS\Since("1.0.0")
     */
    protected $avatar;

    /**
     * @var File
     *
     * @Vich\UploadableField(fileNameProperty="avatar", mapping="user_avatar")
     *
     * @Assert\Image(groups={"Profile"})
     */
    protected $avatarFile;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="FunPro\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="id", onDelete="SET NULL")
     * @Gedmo\Blameable(on="create")
     *
     * @JS\Groups({"CreatedBy"})
     * @JS\Since("1.0.0")
     * @JS\MaxDepth(1)
     */
    protected $createdBy;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @Gedmo\Timestampable(on="create")
     *
     * @JS\Groups({"Public", "Admin"})
     * @JS\Since("1.0.0")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="update")
     *
     * @JS\Groups({"Public", "Admin"})
     * @JS\Since("1.0.0")
     */
    protected $updatedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="deleted_at", type="datetime", nullable=true)
     *
     * @JS\Groups({"Private", "Admin"})
     * @JS\Since("1.0.0")
     */
    protected $deletedAt;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="FunPro\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="deleted_by", referencedColumnName="id", onDelete="SET NULL")
     * @Gedmo\Blameable(on="change", field="deletedAt")
     *
     * @JS\Groups({"DeletedBy"})
     * @JS\Since("1.0.0")
     * @JS\MaxDepth(1)
     */
    protected $deletedBy;

    /**
     * @var integer
     *
     * @ORM\Column(name="wrong_password_count", type="smallint", options={"default"=0})
     *
     * @JS\Groups({"Admin"})
     * @JS\Since("1.0.0")
     */
    private $wrongPasswordCount;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_multi_device_allowed", type="boolean", options={"default"=true})
     *
     * @JS\Groups({"Admin"})
     * @JS\Since("1.0.0")
     */
    private $multiDeviceAllowed;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="FunPro\UserBundle\Entity\Device", mappedBy="owner")
     *
     * @JS\Groups({"Devices"})
     * @JS\Since("1.0.0")
     */
    private $devices;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="FunPro\UserBundle\Entity\Token", mappedBy="user")
     *
     * @JS\Exclude()
     */
    private $tokens;

    /**
     * @var string
     *
     * @ORM\Column(name="api_key", nullable=true, unique=true)
     *
     * @JS\Groups({"Owner", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $apiKey;

    /**
     * @var integer
     *
     * @ORM\Column(name="credit", type="integer", options={"default"=0})
     *
     * @JS\Groups({"Owner", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $credit;

    /**
     * class constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->devices = new ArrayCollection();
        $this->tokens = new ArrayCollection();
        $this->setWrongPasswordCount(0);
        $this->setMultiDeviceAllowed(true);
        $this->credit = 0;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return User
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get age
     *
     * @return integer
     */
    public function getAge()
    {
        return $this->age;
    }

    /**
     * Set age
     *
     * @param integer $age
     *
     * @return User
     */
    public function setAge($age)
    {
        $this->age = $age;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getBorn()
    {
        return $this->born;
    }

    /**
     * @param \DateTime $born
     */
    public function setBorn(\DateTime $born = null)
    {
        $this->born = $born;
    }

    /**
     * Get sex
     *
     * @return integer
     */
    public function getSex()
    {
        return $this->sex;
    }

    /**
     * Set sex
     *
     * @param integer $sex
     *
     * @return User
     */
    public function setSex($sex)
    {
        $this->sex = $sex;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return User
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @JS\Groups({"Public", "Profile", "Admin"})
     * @JS\Since("1.0.0")
     * @JS\SerializedName("avatar_path")
     * @JS\Type("string")
     * @JS\VirtualProperty()
     *
     * @return string
     */
    public function getAvatarPath()
    {
        return 'http://assets.carzinn.ir/media/cache/resolve/mob_avatar_thumb/' . ($this->getAvatar()?: 'default_avatar.jpg');
    }

    /**
     * Get avatar
     *
     * @return string
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * Set avatar
     *
     * @param string $avatar
     *
     * @return User
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;

        return $this;
    }

    /**
     * @param File $avatar
     *
     * @return File
     */
    public function getAvatarFile(File $avatar = null)
    {
        if (!is_null($avatar)) {
            $this->avatarFile = $avatar;
            $this->setUpdatedAt(new \DateTime());
        }

        return $this->avatarFile;
    }

    /**
     * @param File $avatarFile
     */
    public function setAvatarFile($avatarFile)
    {
        $this->avatarFile = $avatarFile;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return User
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Get deletedAt
     *
     * @return \DateTime
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * Set deletedAt
     *
     * @param \DateTime $deletedAt
     *
     * @return User
     */
    public function setDeletedAt(\DateTime $deletedAt)
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set createdBy
     *
     * @param User $createdBy
     *
     * @return User
     */
    public function setCreatedBy(User $createdBy = null)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get deletedBy
     *
     * @return User
     */
    public function getDeletedBy()
    {
        return $this->deletedBy;
    }

    /**
     * Set deletedBy
     *
     * @param User $deletedBy
     *
     * @return User
     */
    public function setDeletedBy(User $deletedBy = null)
    {
        $this->deletedBy = $deletedBy;

        return $this;
    }

    /**
     * Get wrongPasswordCount
     *
     * @return integer
     */
    public function getWrongPasswordCount()
    {
        return $this->wrongPasswordCount;
    }

    /**
     * Set wrongPasswordCount
     *
     * @param integer $wrongPasswordCount
     *
     * @return User
     */
    public function setWrongPasswordCount($wrongPasswordCount)
    {
        $this->wrongPasswordCount = $wrongPasswordCount;

        return $this;
    }

    /**
     * Add tokens
     *
     * @param Token $tokens
     *
     * @return User
     */
    public function addToken(Token $tokens)
    {
        $this->tokens[] = $tokens;

        return $this;
    }

    /**
     * Remove tokens
     *
     * @param Token $tokens
     */
    public function removeToken(Token $tokens)
    {
        $this->tokens->removeElement($tokens);
    }

    /**
     * Get tokens
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTokens()
    {
        return $this->tokens;
    }

    /**
     * @return boolean
     */
    public function isMultiDeviceAllowed()
    {
        return $this->multiDeviceAllowed;
    }

    /**
     * @param boolean $multiDeviceAllowed
     */
    public function setMultiDeviceAllowed($multiDeviceAllowed)
    {
        $this->multiDeviceAllowed = $multiDeviceAllowed;
    }

    /**
     * @return ArrayCollection
     */
    public function getDevices()
    {
        return $this->devices;
    }

    /**
     * @param ArrayCollection $devices
     */
    public function setDevices($devices)
    {
        $this->devices = $devices;
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @param string $apiKey
     *
     * @return $this
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    /**
     * @return int
     */
    public function getCredit()
    {
        return $this->credit;
    }

    /**
     * @param int $credit
     *
     * @return $this
     */
    public function setCredit($credit)
    {
        $this->credit = $credit;
        return $this;
    }
}
