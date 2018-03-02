<?php

namespace App\Repository;

use App\Entity\SearchRequest;
use App\Entity\SearchResult;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class SearchResultRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SearchResult::class);
    }

    /**
     * @param SearchRequest $request
     * @return \Doctrine\ORM\Query
     */
    public function createQueryForPagination(SearchRequest $request)
    {
        return $this->createQueryBuilder('self')
            ->join('self.hotel', 'hotel')
            ->leftJoin('self.meal', 'meal')
            ->addSelect('hotel')
            ->addSelect('meal')
            ->where('self.request = ?0')
            ->setParameters([$request->getId()])
            ->getQuery()
        ;
    }
}
