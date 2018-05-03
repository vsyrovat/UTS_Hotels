<?php

namespace App\Controller;

use App\Service\CustomResultSetFactory;
use App\Entity\SearchRequest;
use App\Entity\SearchResult;
use App\Form\SearchRequestType;
use App\Repository\SearchResultRepository;
use App\Service\HotelSearch;
use App\Service\SearchResultBuilder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TestController
 * @package App\Controller
 */
class TestController extends Controller
{
    private $builder;

    public function __construct(SearchResultBuilder $builder)
    {
        $this->builder = $builder;
    }

    /**
     * @Route("/test", name="test")
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $form = $this->createForm(SearchRequestType::class, new SearchRequest());
        $form->handleRequest($request);
        $hasSearchError = false;
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var SearchRequest $searchRequest */
            $searchRequest = $form->getData();
            $searchService = $this->get(HotelSearch::class);
            if ($searchService->search($searchRequest)) {
                return $this->redirectToRoute('test_results', ['searchRequest' => $searchRequest->getId(), 'page' => 1]);
            } else {
                $hasSearchError = true;
            }
        }
        return $this->render(
            'Test/index.html.twig',
            [
                'form' => $form->createView(),
                'hasSearchError' => $hasSearchError
            ]
        );
    }

    /**
     * @Route("/test/results/{searchRequest}/{page}", name="test_results", requirements={"searchRequest"="\d+", "page"="\d+"}, defaults={"page"=1})
     * @param SearchRequest $searchRequest
     * @param int $page
     * @return Response
     */
    public function results(SearchRequest $searchRequest, int $page = 1)
    {
        $templateVars = array(
            'form' => $this
                ->createForm(
                    SearchRequestType::class,
                    $searchRequest,
                    ['action' => $this->generateUrl('test')]
                )
                ->createView(),
            'request' => $searchRequest
        );
        if ($searchRequest->isCompleted()) {
            /** @var SearchResultRepository $repository */
            $query = $this->builder->buildHotelSetByRequest($searchRequest);
            $paginator = $this->get('knp_paginator');
            $templateVars['pagination'] = $paginator->paginate($query, $page, 10);
        }


        return $this->render('Test/results.html.twig', $templateVars);
    }
}
