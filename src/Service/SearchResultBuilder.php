<?php declare(strict_types=1);

namespace App\Service;

use App\Entity\City;
use App\Entity\Country;
use App\Entity\Discount;
use App\Entity\Money;
use App\Entity\SpecialOffer;
use App\Entity\Virtual\CustomSearchResult;
use App\Entity\Hotel;
use App\Entity\SearchRequest;
use App\Entity\SearchResult;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;

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
        $sql = <<<SQL
SELECT hotel.*,
       city.name AS city_name,
       city.country_id,
       country.name AS country_name,
       MIN(srs.price_rub) AS min_price_rub,
       MIN(CASE
           WHEN so.discount_type='a' THEN (srs.price_rub - so.discount_value)
           WHEN so.discount_type='m' THEN srs.price_rub * (1 - so.discount_value/100)
           ELSE srs.price_rub
       END) AS min_discount_price_rub
FROM (SELECT sr.*,
             sr.price_amount * currency.rate AS price_rub,
             h.city_id,
             c.country_id
      FROM search_result AS sr
      JOIN hotel AS h ON (sr.hotel_id=h.id)
      JOIN city AS c ON (h.city_id=c.id)
      JOIN currency ON (currency.id=sr.price_currency)
      WHERE sr.request_id=:requestId
      ) AS srs
LEFT JOIN (SELECT *
           FROM special_offer
           WHERE is_active=1
           ) AS so
           ON (so.country_id=srs.country_id AND
              (so.city_id  IS NULL OR (so.city_id=srs.city_id AND
              (so.hotel_id IS NULL OR so.hotel_id=srs.hotel_id))))
JOIN hotel ON (hotel.id=srs.hotel_id)
JOIN city ON (city.id=hotel.city_id)
JOIN country ON (country.id=city.country_id)
GROUP BY srs.hotel_id
ORDER BY min_discount_price_rub
SQL;

        $rsm = new ResultSetMapping();
        $rsm->addEntityResult(Hotel::class, 'hotel');
        $rsm->addFieldResult('hotel', 'id', 'id');
        $rsm->addFieldResult('hotel', 'name', 'name');
        $rsm->addJoinedEntityResult(City::class, 'city', 'hotel', 'city');
        $rsm->addFieldResult('city', 'city_id', 'id');
        $rsm->addFieldResult('city', 'city_name', 'name');
        $rsm->addJoinedEntityResult(Country::class, 'country', 'city', 'country');
        $rsm->addFieldResult('country', 'country_id', 'id');
        $rsm->addFieldResult('country', 'country_name', 'name');
        $rsm->addScalarResult('min_price_rub', 'min_price_rub');
        $rsm->addScalarResult('min_discount_price_rub', 'min_discount_price_rub');

        $query = $this->em->createNativeQuery($sql, $rsm);
        $query->setParameters([
            'requestId' => $request->getId(),
        ]);

        $searchSet = array_map(
            function($row) use ($request) {
                list($hotel, $minPriceRub, $minDiscountPriceRub) = array_values($row);
                return (new CustomSearchResult())
                    ->setRequest($request)
                    ->setHotel($hotel)
                    ->setMinPrice(new Money(number_format(floatval($minDiscountPriceRub), 2, '.', ''), 'RUB'));
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
            /* @var $csr \App\Entity\Virtual\CustomSearchResult */
            $set = array_filter($searchResults, function(SearchResult $sr) use ($csr){
                return $sr->getHotel()->getId() === $csr->getHotel()->getId();
            });
            $csr->setSearchResults($set);
            $csr->setMinPrice(($s = reset($set))->getOfferPrice() ?: $s->getPrice());
        }
    }

    /**
     * @param SpecialOffer[] $offers
     * @param SearchResult $searchResult
     * @return array
     */
    private function getBestOfferForSearchResult(array $offers, SearchResult $searchResult): array
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
