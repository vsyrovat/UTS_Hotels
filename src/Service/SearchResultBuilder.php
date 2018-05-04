<?php declare(strict_types=1);

namespace App\Service;

use App\Entity\Money;
use App\Entity\Virtual\CustomSearchResult;
use App\Entity\Hotel;
use App\Entity\SearchRequest;
use App\Entity\SearchResult;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr;

class SearchResultBuilder
{
    /* @var \Doctrine\ORM\EntityManagerInterface */
    private $em;

    private $rater;

    public function __construct(EntityManagerInterface $em, CurrencyRater $rater)
    {
        $this->em = $em;
        $this->rater = $rater;
    }

    /**
     * @param SearchRequest $request
     * @return CustomSearchResult[]
     */
    public function buildHotelSetByRequest(SearchRequest $request): array
    {
        $query = $this->em->createQueryBuilder()
            ->select('h')
            ->addSelect('MIN(sr.price.amount)')
            ->addSelect('MIN(sr.price.currency)')
            ->from(Hotel::class, 'h')
            ->join(SearchResult::class, 'sr', Expr\Join::WITH, 'h.id=sr.hotel')
            ->where('sr.request = ?0')
            ->groupBy('sr.hotel')
            ->orderBy('MIN(sr.price.amount * (CASE
                  WHEN sr.price.currency=\'RUB\' THEN 1
                  WHEN sr.price.currency=\'EUR\' THEN ?1
                  WHEN sr.price.currency=\'GBP\' THEN ?2
                  WHEN sr.price.currency=\'USD\' THEN ?3
                  ELSE 1000
                END))')
            ->setParameters([
                $request->getId(),
                $this->rater->getRate('EUR'),
                $this->rater->getRate('GBP'),
                $this->rater->getRate('USD'),
            ])
            ->getQuery()
        ;
        $searchSet = array_map(
            function($row) use ($request) {
                list($hotel, $minPrice, $currency) = $row;
                return (new CustomSearchResult())
                    ->setRequest($request)
                    ->setHotel($hotel)
                    ->setMinPrice(new Money($minPrice, $currency));
            },
            $query->getResult()
        );

        return $searchSet;
    }

    /**
     * @param CustomSearchResult[] $searchSet
     */
    public function applySearchResults(array $searchSet): void
    {
        if (empty($searchSet)) {
            return;
        }

        $firstItem = reset($searchSet);
        $qb = $this->em->createQueryBuilder();

        $searchResults = $qb
            ->select('sr')
            ->from(SearchResult::class, 'sr')
            ->where($qb->expr()->andX(
                $qb->expr()->eq('sr.request', '?0'),
                $qb->expr()->in('sr.hotel', '?1')
            ))
            ->setParameters([
                $firstItem->getRequest()->getId(),
                array_map(function(CustomSearchResult $csr){ return $csr->getHotel()->getId(); }, $searchSet)
            ])
            ->orderBy('sr.price.amount')
            ->getQuery()
            ->getResult()
        ;
        foreach ($searchSet as $csr) {
            $set = [];
            /* @var $csr \App\Entity\Virtual\CustomSearchResult */
            foreach ($searchResults as $searchResult) {
                /* @var $searchResult \App\Entity\SearchResult */
                if ($searchResult->getHotel()->getId() === $csr->getHotel()->getId()) {
                    $set[] = $searchResult;
                }
            }
            $csr->setSearchResults($set);
        }
    }
}
