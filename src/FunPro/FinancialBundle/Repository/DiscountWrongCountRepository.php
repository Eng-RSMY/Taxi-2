<?php

namespace FunPro\FinancialBundle\Repository;

use Doctrine\ORM\EntityRepository;
use FunPro\PassengerBundle\Entity\Passenger;

/**
 * DiscountWrongCountRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class DiscountWrongCountRepository extends EntityRepository
{
    public function getCount(Passenger $passenger, $timeRange)
    {
        $time = (new \DateTime())->setTimestamp(time() - $timeRange);
        $qb = $this->createQueryBuilder('dc');

        return $qb->select('count(dc) as total')
            ->where($qb->expr()->eq('dc.user', ':user'))
            ->andWhere($qb->expr()->gte('dc.createdAt', ':beginTime'))
            ->setParameter('user', $passenger)
            ->setParameter('beginTime', $time)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
