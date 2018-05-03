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

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getHotelListByRequest(SearchRequest $request)
    {
        $query = $this->em->createQueryBuilder()
            ->select('h')
            ->addSelect('MIN(sr.price.amount)')
            ->addSelect('MIN(sr.price.currency)')
            ->from(Hotel::class, 'h')
            ->join(SearchResult::class, 'sr', Expr\Join::WITH, 'h.id=sr.hotel')
            ->where('sr.request = ?0')
            ->groupBy('sr.hotel')
            ->orderBy('MIN(sr.price.amount)')
            ->setParameters([$request->getId()])
            ->getQuery()
        ;
        $hotelSet = array_map(
            function($row) use ($request) {
                list($hotel, $minPrice, $currency) = $row;
                return (new CustomSearchResult())
                    ->setRequest($request)
                    ->setHotel($hotel)
                    ->setMinPrice(new Money($minPrice, $currency));
            },
            $query->getResult()
        );

        return $hotelSet;
    }
}
