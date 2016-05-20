<?php

namespace FunPro\ServiceBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use FunPro\DriverBundle\Entity\Car;
use FunPro\GeoBundle\Doctrine\ValueObject\Point;
use FunPro\ServiceBundle\Entity\Wakeful;

/**
 * WakefulRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class WakefulRepository extends EntityRepository
{
    /**
     * @param $longitude
     * @param $latitude
     * @param int $distance in kilometer
     * @return Wakeful[]
     */
    public function getAllWakefulNearTo($longitude, $latitude, $distance=2000, $limit=500)
    {
        $qb = $this->createQueryBuilder('w');

        $qb->select(array('w', 'c', 'd'))
            ->join('w.car', 'c')
            ->join('c.driver', 'd')
            ->where($qb->expr()->lte('distance(w.point, point_str(:point))', ':distance'))
            ->andWhere($qb->expr()->eq('c.status', ':status'))
            ->setParameter('point', new Point($longitude, $latitude))
            ->setParameter('distance', $distance)
            ->setParameter('status', Car::STATUS_WAKEFUL);

        $wakefuls = $qb->getQuery()
            ->setMaxResults($limit)
            ->getResult();

        return $wakefuls;
    }
}
