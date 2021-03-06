<?php

namespace FunPro\EngineBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use FunPro\PassengerBundle\Entity\Passenger;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CreatePassengerForTest
 *
 * @package FunPro\EngineBundle\DataFixtures\ORM
 */
class CreatePassengerForTest extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var Container
     */
    private $container;

    /**
     * Sets the container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        if (!$this->container->getParameter('test.passenger')) {
            return;
        }

        $credentials = $this->container->getParameter('test.passenger.credentials');
        $host = $this->container->getParameter('router.request_context.host');
        $i = 1;

        foreach ($credentials as $mobile => $pass) {
            $passenger = new Passenger();
            $passenger->setEmail($mobile.'@'.$host);
            $passenger->setUsername($mobile);
            $passenger->setPlainPassword($pass);
            $passenger->setMobile($mobile);
            $passenger->setName('passenger-'.$i);
            $passenger->setEnabled(true);
            $manager->persist($passenger);
            $this->setReference('passenger-'.$i, $passenger);
            $i++;
        }

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 20;
    }
}
