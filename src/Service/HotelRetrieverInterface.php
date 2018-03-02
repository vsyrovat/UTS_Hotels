<?php

namespace App\Service;


use App\Entity\SearchRequest;

interface HotelRetrieverInterface
{
    /**
     * @param SearchRequest $request
     * @return mixed
     */
    public function getByRequest(SearchRequest $request);
}