<?php declare(strict_types=1);

namespace App\Service;

use App\Entity\Discount;
use App\Entity\Money;
use App\Entity\SpecialOffer;
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
        $offers = $this->em->getRepository(SpecialOffer::class)->findBy(['isActive' => true]);

        foreach ($searchResults as $searchResult) {
            /* @var $searchResult \App\Entity\SearchResult */
            list($bestOffer, $discountPercent) = $this->getBestOfferForSearchResult($offers, $searchResult);
            if ($bestOffer) {
                $searchResult->setOffer($bestOffer);
                $searchResult->setOfferPrice(new Money(
                    strval(floatval($searchResult->getPrice()->getAmount()) * (1 - $discountPercent / 100)),
                    $searchResult->getPrice()->getCurrency()
                ));
            }
        }
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

    /**
     * @param SpecialOffer[] $offers
     * @param SearchResult $searchResult
     * @return array
     */
    private function getBestOfferForSearchResult(array $offers, SearchResult &$searchResult): array
    {
        $bestOffer = null;
        $offerWeightFinal = 0;
        $discountPercentFinal = 0;

        foreach ($offers as $offer) {
            /* @var $offer \App\Entity\SpecialOffer */
            if ($offer->getCountry() && $offer->getCountry()->getId() != $searchResult->getHotel()->getCity()->getCountry()->getId() ||
                $offer->getCity() && $offer->getCity()->getId() != $searchResult->getHotel()->getCity()->getId() ||
                $offer->getHotel() && $offer->getHotel()->getId() != $searchResult->getHotel()->getId()
            ) {
                continue;
            }

            $discount = $offer->getDiscount();
            $discountPercent = $discount->getType() === Discount::DISCOUNT_TYPE_ABSOLUTE
                ? 100 * $discount->getValue() / ($searchResult->getPrice()->getAmount() * $this->rater->getRate($searchResult->getPrice()->getCurrency()))
                : $discount->getValue();
            $offerWeight = $discountPercent +
                (!empty($offer->getCountry()) && ($offer->getCountry()->getId() == $searchResult->getHotel()->getCity()->getCountry()->getId())) * 100 +
                (!empty($offer->getCity()) && ($offer->getCity()->getId() == $searchResult->getHotel()->getCity()->getId())) * 1000 +
                (!empty($offer->getHotel()) && ($offer->getHotel()->getId() == $searchResult->getHotel()->getId())) * 10000;
            if ($offerWeight > $offerWeightFinal) {
                $bestOffer = $offer;
                $offerWeightFinal = $offerWeight;
                $discountPercentFinal = $discountPercent;
            }
        }

        return [$bestOffer, $discountPercentFinal];
    }
}
