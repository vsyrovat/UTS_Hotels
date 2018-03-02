<?php

namespace App\Controller;

use App\Entity\SpecialOffer;
use App\Form\SpecialOfferType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SpecialOfferController extends Controller
{
    /**
     * @Route("/special/offer", name="special_offer", methods={"GET"})
     */
    public function index()
    {
        $repo = $this->getDoctrine()
            ->getManagerForClass(SpecialOffer::class)
            ->getRepository(SpecialOffer::class)
        ;

        return $this->render('SpecialOffer/list.html.twig', [ 'entities' => $repo->findAll()]);
    }

    /**
     * @Route("/special/offer/edit/{offer}", name="special_offer_edit", requirements={"offer"="\d+"})
     * @param SpecialOffer $offer
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function edit(SpecialOffer $offer, Request $request)
    {
        $form = $this->createForm(SpecialOfferType::class, $offer);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManagerForClass(SpecialOffer::class);
            $em->persist($offer);
            $em->flush();

            return $this->redirectToRoute('special_offer');
        }

        return $this->render('SpecialOffer/edit.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/special/offer/add", name="special_offer_add")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function add(Request $request)
    {
        $form = $this->createForm(SpecialOfferType::class, new SpecialOffer());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManagerForClass(SpecialOffer::class);
            $em->persist($form->getData());
            $em->flush();

            return $this->redirectToRoute('special_offer');
        }

        return $this->render('SpecialOffer/edit.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/special/offer/delete/{offer}", name="special_offer_delete", requirements={"offer"="\d+"})
     * @param SpecialOffer $offer
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function delete(SpecialOffer $offer)
    {
        $em = $this->getDoctrine()->getManagerForClass(SpecialOffer::class);
        $em->remove($offer);
        $em->flush();

        return $this->redirectToRoute('special_offer');
    }
}
