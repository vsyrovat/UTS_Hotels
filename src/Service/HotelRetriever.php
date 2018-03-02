<?php

namespace App\Service;

use App\DataTransferObject\SearchParams;
use App\DataTransferObject\SearchParamsRoom;
use App\DataTransferObject\SearchResponse;
use App\DataTransferObject\SearchResult as ApiSearchResult;
use App\DataTransferObject\SearchResultRoom;
use App\DataTransferObject\SearchResultRoomList;
use App\Entity\Hotel;
use App\Entity\Meal;
use App\Entity\Money;
use App\Entity\SearchRequest;
use App\Entity\SearchResult;
use Doctrine\ORM\EntityManagerInterface;

class HotelRetriever implements HotelRetrieverInterface
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;
    /**
     * @var string
     */
    protected $serviceUrl;
    /**
     * @var \SoapClient
     */
    protected $soapClient;

    /**
     * HotelRetriever constructor.
     * @param EntityManagerInterface $em
     * @param string $serviceUrl
     */
    public function __construct(EntityManagerInterface $em, string $serviceUrl)
    {
        $this->em = $em;
        $this->serviceUrl = $serviceUrl;
    }

    /**
     * @return \SoapClient
     */
    protected function getSoapClient()
    {
        if(!$this->soapClient){
            $this->soapClient = new \SoapClient(
                $this->serviceUrl,
                array(
                    'features' => SOAP_SINGLE_ELEMENT_ARRAYS,
                    'trace' => true,
                    'exceptions' => true,
                    'classmap' => [
                        'ArrayOfSearchResult' => SearchResponse::class,
                        'SearchResult' => ApiSearchResult::class,
                        'ArrayOfSearchResultRoom' => SearchResultRoomList::class,
                        'SearchResultRoom' => SearchResultRoom::class
                    ]
                )
            );
        }
        return $this->soapClient;
    }


    /**
     * @param SearchRequest $request
     * @return SearchResult[]|array|mixed
     */
    public function getByRequest(SearchRequest $request)
    {
        $params = $this->makeSearchParams($request);
        /** @var SearchResponse $response */
        $response = $this->getSoapClient()->__soapCall('search', [$params]);
        if (empty($response->item)) {
            return [];
        }
        list($hotels, $meals) = $this->prepareData($response);
        
        return $this->makeResults($request, $response, $hotels, $meals);
    }

    /**
     * @param SearchRequest $request
     * @return SearchParams
     */
    protected function makeSearchParams(SearchRequest $request)
    {
        $room = new SearchParamsRoom($request->getAdults(), 1);
        $diff = $request->getCheckIn()->diff($request->getCheckOut());
        return new SearchParams(
            $request->getCity()->getId(),
            $request->getCheckIn()->format('Y-m-d'),
            $diff->days,
            [$room]
        );
    }

    /**
     * @param SearchResponse $response
     * @return array
     */
    protected function prepareData(SearchResponse $response)
    {
        $hotelIds = $hotels = $meals = [];
        $mealIds = [
            Meal::MEAL_UNKNOWN => true
        ];
        foreach ($response->item as $item) {
            $hotelIds[$item->hotelId] = true;
            $mealIds[$item->mealId] = true;
        }
        $entities = $this->em
            ->getRepository(Hotel::class)
            ->findBy(['id' => array_keys($hotelIds)])
        ;
        foreach ($entities as $entity) {
            /** @var $entity Hotel */
            $hotels[$entity->getId()] = $entity;
        }
        $entities = $this->em
            ->getRepository(Meal::class)
            ->findBy(['id' => array_keys($mealIds)])
        ;
        foreach ($entities as $entity) {
            /** @var $entity Meal */
            $meals[$entity->getId()] = $entity;
        }
        
        return [$hotels, $meals];
    }

    /**
     * @param SearchRequest $request
     * @param SearchResponse $response
     * @param Hotel[] $hotels
     * @param Meal[] $meals
     * @return SearchResult[]
     */
    protected function makeResults(SearchRequest $request, SearchResponse $response, $hotels, $meals)
    {
        $results = [];
        foreach ($response->item as $item) {
            if(!isset($hotels[$item->hotelId]) ||
                empty($item->rooms->item) ||
                count($item->rooms->item) != 1 ||
                $item->rooms->item[0]->roomNumber != 1
            ){
                /*
                 * Отсеиваем нестандартные варианты -
                 * не знаем такой отель или номеров неадекватное количество
                 * (мы то больше одного сейчас не просим)
                 */
                continue;
            }

            $result = new SearchResult();
            $result->setHotel($hotels[$item->hotelId]);
            $result->setPrice(new Money($item->price, $item->currency));
            $result->setRoomName($item->rooms->item[0]->roomName);
            $result->setRequest($request);
            $result->setMeal(isset($meals[$item->mealId]) ? $meals[$item->mealId] : $meals[Meal::MEAL_UNKNOWN]);
            $results[] = $result;
        }
        
        return $results;
    }
}