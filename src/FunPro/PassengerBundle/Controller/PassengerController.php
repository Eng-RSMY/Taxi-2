<?php

namespace FunPro\PassengerBundle\Controller;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\NoResultException;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use FunPro\UserBundle\Entity\Token;
use FunPro\PassengerBundle\Entity\Passenger;
use FunPro\PassengerBundle\Form\Type\RegisterType;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type;
use FOS\RestBundle\Context\Context;

/**
 * Class PassengerController
 *
 * @package FunPro\PassengerBundle\Controller
 *
 * @Rest\RouteResource("passenger", pluralize=false)
 * @Rest\NamePrefix("fun_pro_passenger_api_")
 */
class PassengerController extends FOSRestController
{
    /**
     * Show a form for create of passenger
     *
     * @Rest\View(template="FunProPassengerBundle:Passenger:new.html.twig")
     *
     * @return \Symfony\Component\Form\Form
     */
    public function newAction()
    {
    }

    /**
     * Create a passenger
     *
     * @ApiDoc(
     *      section="Register",
     *      resource=true,
     *      views={"passenger"},
     *      statusCodes={
     *          204="When success",
     *          400="you send very request for token(code: 1)",
     *          403= {
     *              "when you are a user and you are login currently",
     *          },
     *      }
     * )
     *
     * @Security("!is_authenticated()")
     *
     * @Rest\View()
     * @Rest\RequestParam(name="phone", requirements="09[0-9]{9}", strict=true)
     *
     * @return \FOS\RestBundle\View\View
     */
    public function postAction()
    {
        $logger = $this->get('logger');
        $manager = $this->getDoctrine()->getManager();
        $phone = $this->get('fos_rest.request.param_fetcher')->get('phone');
        $passenger = $this->getDoctrine()->getRepository('FunProPassengerBundle:Passenger')->findOneByMobile($phone);

        if ($passenger === null) {
            $passenger = new Passenger();
            $passenger->setMobile($phone);
            $passenger->setPassword(bin2hex(random_bytes(10)));
            $manager->persist($passenger);
            $manager->flush();
            $logger->addInfo('passenger persisted', array('id' => $passenger->getId()));
        }

        $period = new \DateTime('-' . $this->getParameter('register.reset_token_request_after_second') . 'seconds');
        $tokenRequestedCount = $manager->getRepository('FunProUserBundle:Token')
            ->getTokenCount($passenger, $period);

        if ($tokenRequestedCount > $this->getParameter('register.max_token_request')) {
            $logger->addWarning('you have very token request', [
                'count' => $tokenRequestedCount,
                'max' => $this->getParameter('register.max_token_request'),
                'period' => $this->getParameter('register.reset_token_request_after_second'),
            ]);
            $error = array(
                'code' => 1,
                'message' => $this->get('translator')->trans('you.have.very.request.for.token'),
            );
            return $this->view($error, Response::HTTP_BAD_REQUEST);
        }

        do {
            try {
                $verifyNumber = random_int(11111, 99999);
                $token = new Token($verifyNumber);
                $token->setUser($passenger);
                $this->getDoctrine()->getManager()->persist($token);
                $this->getDoctrine()->getManager()->flush();
                $tokenIsValid = true;
            } catch (UniqueConstraintViolationException $e) {
                $tokenIsValid = false;
            }
        } while (!$tokenIsValid);
        $logger->addInfo('token persisted', array('token' => $verifyNumber));
        $this->get('sms.sender')->send($passenger->getMobile(), $verifyNumber);

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Confirm register & login
     *
     * @ApiDoc(
     *      section="Register",
     *      resource=true,
     *      views={"passenger"},
     *      output={
     *          "class"="FunPro\PassengerBundle\Entity\Passenger",
     *          "groups"={"Public"},
     *          "parsers"={"Nelmio\ApiDocBundle\Parser\JmsMetadataParser"},
     *      },
     *      statusCodes={
     *          201="When success",
     *          204="When token is reset",
     *          400={
     *              "you send very request for token(code: 1)",
     *              "Token is not exists(code: 2)",
     *              "Token is expire(code: 3)",
     *          },
     *          403= {
     *              "when csrf token is invalid",
     *              "when you are a user and you are login in currently",
     *          },
     *          404={
     *              "When user is not exists(code: 1)",
     *          },
     *      }
     * )
     *
     * @Security("!is_authenticated()")
     *
     * @Rest\RequestParam(name="token", requirements="\d+", strict=true, description="token number for verify")
     * @Rest\RequestParam(name="phone", requirements="09[0-9]{9}", strict=true, description="phone number")
     * @Rest\RequestParam(name="referer", requirements="09[0-9]{9}", strict=true, nullable=true, description="referer phone number")
     */
    public function postConfirmAction()
    {
        $logger = $this->get('logger');
        $translator = $this->get('translator');
        $fetcher = $this->get('fos_rest.request.param_fetcher');
        $phone = $fetcher->get('phone');
        $token = $fetcher->get('token');
        $referer = $fetcher->get('referer');

        $manager = $this->getDoctrine()->getManager();

        /** @var Passenger $user */
        $user = $manager->getRepository('FunProPassengerBundle:Passenger')->findOneByMobile($phone);

        if (!$user) {
            $logger->addNotice('user is not exists', array('username' => $phone));
            $error = array(
                'code' => 1,
                'message' => $translator->trans('user.is.not.exists'),
            );
            return $this->view($error, Response::HTTP_NOT_FOUND);
        }

        if ($user->getWrongTokenCount() >= $this->getParameter('login.max_failure_count')) {
            $logger->addNotice('You send very request with wrong token');
            $response = $this->postAction();
            $user->resetWrongTokenCount();
            $manager->flush();
            return $response;
        }

        try {
            $tokenObject = $this->getDoctrine()->getRepository('FunProUserBundle:Token')
                ->getToken($user, $token);

            $now = new \DateTime();
            $diff = $now->diff($tokenObject->getCreatedAt());

            if ($tokenObject->isExpired() or $diff->days >= 1) {
                $logger->addNotice('your token was expired', array('token' => $tokenObject->getToken()));
                $error = [
                    'code' => 3,
                    'message' => $translator->trans('your.token.expired'),
                ];
                return $this->view($error, Response::HTTP_BAD_REQUEST);
            }
        } catch (NoResultException $e) {
            $logger->addNotice('token is not exists');
            $user->increaseWrongTokenCount();
            $manager->flush();
            $error = array(
                'code' => 2,
                'message' => $translator->trans('token.is.not.exists'),
            );
            return $this->view($error, Response::HTTP_BAD_REQUEST);
        }

        do {
            $apiKey = bin2hex(random_bytes(100));
            $isDuplicate = $manager->getRepository('FunProUserBundle:Device')
                ->findOneByApiKey($apiKey);
        } while ($isDuplicate);

        $manager->getConnection()->beginTransaction();
        $tokenObject->setExpired(true);
        $user->setApiKey($apiKey);
        $user->resetWrongTokenCount();
        $user->setUsername($user->getMobile());
        $this->get('fos_user.user_manager')->updateUser($user);
        if ($user->getReferrer() === null and $referer !== null) {
            $userReferer = $manager->getRepository('FunProPassengerBundle:Passenger')->findOneByMobile($referer);
            if ($userReferer) {
                $logger->addInfo('set user referer', array('referer' => $referer));
                $user->setReferrer($userReferer);
                #TODO: add point to user(referer) wallet
            }
        }
        $manager->getConnection()->commit();
        $logger->addInfo('registration process completed');

        $context = (new Context())
            ->addGroups(array('Owner', 'Devices', 'Public', 'Register'))
            ->setMaxDepth(2);
        return $this->view($user, Response::HTTP_CREATED)
            ->setSerializationContext($context);
    }

    /**
     * @param $passengerId
     *
     * @Rest\Get(requirements={"passengerId"="\d+"})
     */
    public function getAction($passengerId)
    {
    }
}
