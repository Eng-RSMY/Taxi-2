<?php

namespace FunPro\FinancialBundle\Repository;

use CrEOF\Spatial\PHP\Types\Geometry\Point;
use Doctrine\ORM\EntityRepository;
use FunPro\FinancialBundle\Entity\Currency;

/**
 * RegionBasePriceRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class RegionBasePriceRepository extends EntityRepository
{
    public function getPriceInRegion(Point $point, Currency $currency)
    {
        $queryBuilder = $this->createQueryBuilder('rbp');

        return $queryBuilder->select(array('rbp'))
            ->innerJoin('rbp.region', 'r')
            ->where('ST_Contains(r.region, geomfromtext(:point)) = true')
            ->andWhere($queryBuilder->expr()->eq('rbp.currency', ':currency'))
            ->setParameter('currency', $currency)
            ->setParameter('point', $point)
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }
}
