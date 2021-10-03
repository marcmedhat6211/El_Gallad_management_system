<?php

namespace PN\Bundle\UserBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use PN\ServiceBundle\Model\DateTimeTrait;
use PN\ServiceBundle\Model\VirtualDeleteTrait;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="usr")
 * @ORM\Entity(repositoryClass="PN\Bundle\UserBundle\Repository\UserRepository")
 */
class User extends BaseUser {

    use DateTimeTrait,
        VirtualDeleteTrait;

    const ROLE_ADMIN = "ROLE_ADMIN";
    const ROLE_IMAGE_GALLERY = "ROLE_IMAGE_GALLERY";

    static public $rolesList = [
        'Admin' => self::ROLE_ADMIN,
        'Manage Image Gallery' => self::ROLE_IMAGE_GALLERY,
    ];

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @Assert\NotBlank()
     *
     * @var string
     * @ORM\Column(name="full_name", type="string", length=255)
     */
    protected $fullName;

    /**
     * @Assert\Regex("/^[0-9\(\)\/\+ \-]+$/i")
     * @var string
     * @ORM\Column(name="phone", type="string", length=15 ,nullable=true)
     */
    protected $phone;

    /**
     * @ORM\Column(name="facebook_id", type="string", length=255, nullable=true)
     */
    private $facebookId;

    /**
     * Now we tell doctrine that before we persist or update we call the updatedTimestamps() function.
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updatedTimestamps() {
        $this->setModified(new \DateTime(date('Y-m-d H:i:s')));

        if ($this->getCreated() == null) {
            $this->setCreated(new \DateTime(date('Y-m-d H:i:s')));
        }
    }

    public function getObj() {
        $obj = [
            'apiKey' => (string) $this->getApiKey(),
            'name' => (string) $this->getFullName(),
            'email' => (string) $this->getEmail(),
            'phone' => (string) $this->getPhone(),
        ];

        return $obj;
    }

    public function __construct() {
        parent::__construct();
        // your own logic
    }

    public function setEmail($email) {
        parent::setEmail($email);
        $this->setUsername($email);

        return $this;
    }

    public function getRole() {
        $roles = $this->getRoles();
        if (count($roles) > 0) {
            return $roles[0];
        }
        return NULL;
    }

    public function getRoleName() {
        $role = $this->getRole();
        return array_search($role, self::$rolesList);
    }

    public function setRole($role) {
        foreach ($this->getRoles() as $oldRole) {
            $this->removeRole($oldRole);
        }

        $this->addRole($role);
        return $this;
    }

    /**
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set fullName
     *
     * @param string $fullName
     * @return User
     */
    public function setFullName($fullName) {
        $this->fullName = $fullName;

        return $this;
    }

    /**
     * Get fullName
     *
     * @return string
     */
    public function getFullName() {
        return $this->fullName;
    }

    /**
     * Set phone
     *
     * @param string $phone
     *
     * @return User
     */
    public function setPhone($phone) {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone() {
        return $this->phone;
    }

    /**
     * Set birthdate
     *
     * @param \DateTime $birthdate
     *
     * @return User
     */
    public function setBirthdate($birthdate) {
        $this->birthdate = $birthdate;

        return $this;
    }

    /**
     * Get birthdate
     *
     * @return \DateTime
     */
    public function getBirthdate() {
        return $this->birthdate;
    }

    /**
     * Set facebookId
     *
     * @param string $facebookId
     *
     * @return User
     */
    public function setFacebookId($facebookId) {
        $this->facebookId = $facebookId;

        return $this;
    }

    /**
     * Get facebookId
     *
     * @return string
     */
    public function getFacebookId() {
        return $this->facebookId;
    }


}
