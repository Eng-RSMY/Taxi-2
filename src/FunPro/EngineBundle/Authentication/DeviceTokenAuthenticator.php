<?php

namespace FunPro\EngineBundle\Authentication;

use Doctrine\ORM\EntityManager;
use FunPro\UserBundle\Entity\Device;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

/**
 * Class DeviceTokenAuthenticator
 *
 * @package FunPro\EngineBundle\Authentication
 */
class DeviceTokenAuthenticator extends AbstractGuardAuthenticator
{
    /**
     * @var Device
     */
    private $device;

    /**
     * @var EntityManager
     */
    private $manager;

    /**
     * @param EntityManager $manager
     */
    public function __construct(EntityManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Called on every request. Return whatever credentials you want,
     * or null to stop authentication.
     */
    public function getCredentials(Request $request)
    {
        if (!$token = $request->headers->get('X-AUTH-TOKEN')) {
            return;
        }

        return array(
            'token' => $token,
        );
    }

    /**
     * @param mixed                 $credentials
     * @param UserProviderInterface $userProvider
     *
     * @return \FunPro\UserBundle\Entity\User|null|UserInterface
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $apiKey = $credentials['token'];

        $this->device = $this->manager->getRepository('FunProUserBundle:Device')->findOneByApiKey($apiKey);

        if ($this->device) {
            return $this->device->getOwner();
        }

        return;
    }

    /**
     * @param mixed         $credentials
     * @param UserInterface $user
     *
     * @return bool
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    /**
     * @param Request        $request
     * @param TokenInterface $token
     * @param string         $providerKey
     *
     * @return null|\Symfony\Component\HttpFoundation\Response
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $request->attributes->set('currentDevice', $this->device);
        $this->device->setLastLoginAt(new \DateTime());
        $this->manager->flush();

        return null;
    }

    /**
     * @param Request                 $request
     * @param AuthenticationException $exception
     *
     * @return null|JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $data = array(
            'message' => 'token expired',
            'code' => 498,
        );

        #we can not handle 401 response in volley
        return new JsonResponse($data, 498);
    }

    /**
     * Called when authentication is needed, but it's not sent
     *
     * @param Request                 $request
     * @param AuthenticationException $authException
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $data = array(
            'message' => 'Authentication Required'
        );

        return new JsonResponse($data, 401);
    }

    /**
     * @return bool
     */
    public function supportsRememberMe()
    {
        return false;
    }
}
