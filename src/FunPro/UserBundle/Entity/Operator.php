<?php

namespace FunPro\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Operator
 *
 * @ORM\Entity(repositoryClass="FunPro\UserBundle\Repository\UserRepository")
 * @ORM\Table(name="operator")
 *
 * @package FunPro\UserBundle\Entity
 */
class Operator extends User
{
    public function __construct()
    {
        parent::__construct();
        $this->addRole('ROLE_OPERATOR');
        $this->setEnabled(true);
    }
}
