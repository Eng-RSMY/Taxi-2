<?php

namespace FunPro\FinancialBundle\Controller;

use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FunPro\FinancialBundle\Entity\Transaction;
use FunPro\FinancialBundle\Entity\Wallet;
use FunPro\FinancialBundle\Event\PaymentEvent;
use FunPro\FinancialBundle\Exception\InvalidTransactionException;
use FunPro\FinancialBundle\Exception\LowBalanceException;
use FunPro\FinancialBundle\FinancialEvents;
use FunPro\ServiceBundle\Entity\Service;
use FunPro\ServiceBundle\Entity\ServiceLog;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class PaymentController
 *
 * @package FunPro\FinancialBundle\Controller
 *
 * @Rest\RouteResource("payment", pluralize=false)
 * @Rest\NamePrefix("fun_pro_financial_api_")
 */
class PaymentController extends FOSRestController
{
    /**
     * Get a list of user wallet that have enough balance
     *
     * @deprecated
     *
     * @ApiDoc(
     *      section="Payment",
     *      resource=true,
     *      views={"passenger"},
     *      statusCodes={
     *          200="When success",
     *          200="When any wallet is not appropriate",
     *          400={
     *              "When service is not exists(code: 1)",
     *              "When service status is not end(code: 2)",
     *          },
     *          403= {
     *              "when you are not passenger",
     *              "when you are not passenger of service(code: 1)",
     *          },
     *      }
     * )
     *
     * @Security("has_role('ROLE_PASSENGER')")
     *
     * @Rest\QueryParam(name="serviceId", requirements="\d+", nullable=false, strict=true)
     */
    public function getWalletAction()
    {
        $logger = $this->get('logger');
        $translator = $this->get('translator');
        $fetcher = $this->get('fos_rest.request.param_fetcher');
        $manager = $this->getDoctrine()->getManager();

        $serviceRepo = $manager->getRepository('FunProServiceBundle:Service');
        $service = $serviceRepo->find($fetcher->get('serviceId'));

        if (!$service) {
            $logger->addError('service is not exists,', array('serviceId' => $fetcher->get('serviceId')));
            $error = array(
                'code' => 1,
                'message' => $translator->trans('service.is.not.exists'),
            );
            return $this->view($error, Response::HTTP_BAD_REQUEST);
        }

        if ($service->getPassenger() !== $this->getUser()) {
            $logger->addError(
                'You are not passenger of service',
                array(
                    'serviceId' => $service->getId(),
                    'user' => $this->getUser()->getId(),
                )
            );
            $error = array(
                'code' => 1,
                'message' => $translator->trans('this.service.is.not.requested.by.you'),
            );
            return $this->view($error, Response::HTTP_FORBIDDEN);
        }

        $serviceLogRepo = $manager->getRepository('FunProServiceBundle:ServiceLog');
        if ($serviceLogRepo->getLastLog($service)->getStatus() !== ServiceLog::STATUS_FINISH) {
            $logger->addWarning('options can only check after end of servie', array('serviceId' => $service->getId()));
            $error = array(
                'code' => 2,
                'message' => $translator->trans('you.can.only.check.options.in.end.of.service'),
            );
            return $this->view($error, Response::HTTP_BAD_REQUEST);
        }

        $serviceCost = $serviceRepo->getTotalCost($service);
        $logger->addDebug('service total cost', array('cost' => $serviceCost));

        #FIXME: when agent request one service, passenger is null
        $wallets = $manager->getRepository('FunProFinancialBundle:Wallet')
            ->getPayableWallets($service->getPassenger(), $serviceCost, $service->getStartPoint());

        $statusCode = is_null($wallets) ? Response::HTTP_NO_CONTENT : Response::HTTP_OK;
        $context = (new Context())->addGroups(array('Owner', 'Currency', 'Public'))->setMaxDepth(1);
        return $this->view($wallets, $statusCode)->setSerializationContext($context);
    }

    /**
     * Get currencies available in this region
     *
     * @deprecated
     *
     * @ApiDoc(
     *      section="Payment",
     *      resource=true,
     *      views={"passenger"},
     *      output={
     *          "class"="FunPro\FinancialBundle\Entity\Currency",
     *          "groups"={"Public"},
     *          "parsers"={"Nelmio\ApiDocBundle\Parser\JmsMetadataParser"},
     *      },
     *      statusCodes={
     *          200="When success",
     *          404="When service is not exists",
     *          403= {
     *              "when you don't requested this service",
     *          },
     *      }
     * )
     *
     * @Security("has_role('ROLE_PASSENGER')")
     *
     * @Rest\QueryParam(name="serviceId", requirements="\d+", nullable=false, strict=true)
     *
     * @return \FOS\RestBundle\View\View
     */
    public function getCurrencyAvailableAction()
    {
        $fetcher = $this->get('fos_rest.request.param_fetcher');
        $serviceId = $fetcher->get('serviceId');
        $service = $this->getDoctrine()->getRepository('FunProServiceBundle:Service')->find($serviceId);

        if (!$service) {
            throw $this->createNotFoundException('service is not exists');
        }

        if ($service->getPassenger() !== $this->getUser()) {
            throw $this->createAccessDeniedException('you are not passenger of this service');
        }

        $currencies = $this->getDoctrine()->getRepository('FunProFinancialBundle:Currency')
            ->getAvailableInRegion($service->getStartPoint());

        return $this->view($currencies, Response::HTTP_OK);
    }

    /**
     * Pay of service
     *
     * @ApiDoc(
     *      section="Payment",
     *      resource=true,
     *      views={"passenger"},
     *      output={
     *          "class"="FunPro\FinancialBundle\Entity\Transaction",
     *          "groups"={"Owner"},
     *          "parsers"={"Nelmio\ApiDocBundle\Parser\JmsMetadataParser"},
     *      },
     *      statusCodes={
     *          201="When success",
     *          400={
     *              "This service was payed(code: 1)",
     *              "Your wallet balance is not enough(code: 4)",
     *              "Gateway is not available(code: 5)",
     *              "Service is not finished(code: 6)",
     *          },
     *          403= {
     *              "when you are not a passenger",
     *              "when you are not passenger of service(code: 2)",
     *          },
     *          404={
     *              "Service is not exists(code: 1)",
     *              "Wallet is not exists(code: 2)",
     *          },
     *      }
     * )
     *
     * @Security("has_role('ROLE_PASSENGER')")
     *
     * @Rest\RequestParam(name="serviceId", requirements="\d+", nullable=false, strict=true)
     * @Rest\RequestParam(name="virtual", requirements="1", default="0", nullable=true, strict=true)
     */
    public function postAction()
    {
        $logger = $this->get('logger');
        $translator = $this->get('translator');
        $manager = $this->getDoctrine()->getEntityManager();
        $serializer = $this->get('jms_serializer');
        $fetcher = $this->get('fos_rest.request.param_fetcher');

        $serviceId = $fetcher->get('serviceId');
        $virtual = $fetcher->get('virtual');

        $serviceRepo = $manager->getRepository('FunProServiceBundle:Service');
        $service = $serviceRepo->find($serviceId);

        if (!$service) {
            $logger->addError('Service is not exists', array('serviceId' => $serviceId));
            $error = array(
                'code' => 1,
                'message' => $translator->trans('service.is.not.exists'),
            );
            return $this->view($error, Response::HTTP_NOT_FOUND);
        }

        if ($service->getPassenger() !== $this->getUser()) {
            $logger->addError(
                'You are not passenger of service',
                array(
                    'serviceId' => $serviceId,
                    'user' => $this->getUser()->getId(),
                )
            );
            $error = array(
                'code' => 2,
                'message' => $translator->trans('this.service.is.not.requested.by.you'),
            );
            return $this->view($error, Response::HTTP_FORBIDDEN);
        }

        $log = $manager->getRepository('FunProServiceBundle:ServiceLog')->getLastLog($service);
        if ($log->getStatus() == ServiceLog::STATUS_PAYED) {
            $logger->addWarning('You payed this service');
            $error = array(
                'code' => 1,
                'message' => $translator->trans('you.payed.service'),
            );
            return $this->view($error, Response::HTTP_BAD_REQUEST);
        }

        if ($log->getStatus() !== ServiceLog::STATUS_FINISH) {
            $logger->addError('Service is not finished', array('serviceId' => $serviceId));
            $error = array(
                'code' => 6,
                'message' => $translator->trans('service.is.not.finished'),
            );
            return $this->view($error, Response::HTTP_BAD_REQUEST);
        }

        $cost = $serviceRepo->getTotalCost($service);

        $currency = $manager->getRepository('FunProFinancialBundle:Currency')->findOneByCode('IRR');

        if ($virtual) {
            $wallet = $manager->getRepository('FunProFinancialBundle:Wallet')->findOneBy(array(
                'currency' => $currency,
                'owner' => $this->getUser(),
            ));
            $logger->addInfo('Payment method is credit');

            if (!$wallet) {
                $logger->addError('wallet is not exists', array('user' => $this->getUser()));
                $error = array(
                    'code' => 2,
                    'message' => $translator->trans('wallet.is.not.exists'),
                );
                return $this->view($error, Response::HTTP_NOT_FOUND);
            }

            $service->setCurrency($wallet->getCurrency());
            $method = 'credit';
        } else {
            $logger->addInfo('Payment method is cash');

            $service->setCurrency($currency);
            $method = 'cash';
        }

        $transaction = new Transaction(
            $service->getPassenger(),
            $service->getCurrency(),
            $cost,
            Transaction::TYPE_PAY,
            false
        );

        if ($method === 'credit') {
            if ($wallet->getBalance() < $cost) {
                $logger->addError(
                    'Your wallet balance is not enough',
                    array(
                        'wallet' => $wallet->getId(),
                        'balance' => $wallet->getBalance(),
                        'cost' => $cost,
                    )
                );
                $error = array(
                    'code' => 4,
                    'message' => $translator->trans('your.wallet.balance.is.not.enough'),
                );
                return $this->view($error, Response::HTTP_BAD_REQUEST);
            }

            $transaction->setWallet($wallet);
            $transaction->setVirtual(true);
        }

        $transaction->setService($service);
        $transaction->setStatus(Transaction::STATUS_SUCCESS);

        $errors = $this->get('validator')->validate($transaction, null, array('Create', 'Pay'));
        if (count($errors)) {
            $transactionContext = SerializationContext::create()
                ->setGroups(array('Admin', 'User', 'Service', 'Currency', 'CurrencyLog', 'Wallet', 'Gateway'));
            $logger->addAlert(
                'transaction is not valid',
                array(
                    'errors' => $serializer->serialize($errors, 'json'),
                    'transaction' => $serializer->serialize($transaction, 'json', $transactionContext)
                )
            );
            $error = array(
                'code' => 5,
                'message' => $translator->trans('gateway.is.not.available.plz.pay.with.cash')
            );
            return $this->view($error, Response::HTTP_BAD_REQUEST);
        }

        $manager->persist($transaction);
        $logger->addInfo('main transaction is persisted');

        try {
            $event = new PaymentEvent($transaction);
            $this->get('event_dispatcher')->dispatch(FinancialEvents::PAYMENT_EVENT, $event);
            $manager->flush();
        } catch (InvalidTransactionException $e) {
            $logger->addAlert('Gateway is not available');
            $error = array(
                'code' => 5,
                'message' => $translator->trans('gateway.is.not.available.plz.pay.with.cash')
            );
            return $this->view($error, Response::HTTP_BAD_REQUEST);
        } catch (LowBalanceException $e) {
            $error = array(
                'code' => 4,
                'message' => $translator->trans('your.wallet.balance.is.not.enough'),
            );
            return $this->view($error, Response::HTTP_BAD_REQUEST);
        }

        return $this->view($transaction, Response::HTTP_CREATED);
    }

    /**
     * @Rest\Get(requirements={"id": "\d+"})
     *
     * @param $id
     */
    public function getAction($id)
    {
    }
}
