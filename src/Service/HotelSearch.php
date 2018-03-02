<?php

namespace App\Service;

use App\Entity\SearchRequest;
use Doctrine\ORM\EntityManagerInterface;

class HotelSearch
{
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var HotelRetrieverInterface
     */
    private $retriever;

    /**
     * HotelSearch constructor.
     * @param EntityManagerInterface $em
     * @param HotelRetrieverInterface $retriever
     */
    public function __construct(EntityManagerInterface $em, HotelRetrieverInterface $retriever)
    {
        $this->em = $em;
        $this->retriever = $retriever;
    }

    /**
     * @param SearchRequest $request
     * @return bool
     * @throws
     */
    public function search(SearchRequest $request)
    {
        try {
            $this->em->persist($request);
            $this->em->flush();
            $results = $this->retriever->getByRequest($request);
            array_map([$this->em, 'persist'], $results);
            $request->setStatus(SearchRequest::STATUS_COMPLETE);
            $this->em->flush();
        } catch (\Exception $exception) {
            $request->setStatus(SearchRequest::STATUS_ERROR);
            $this->em->flush();
            throw $exception;
        }

        return $request->getStatus() == SearchRequest::STATUS_COMPLETE;
    }
}