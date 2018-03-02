<?php

namespace App\Controller;

use App\Form\Transformer\EntityToIdTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class DependentDataController extends Controller
{
    /**
     * @Route("/data", name="data")
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }
        $data = [];
        $config = $this->container->getParameter('dependent_classes');
        $alias = $request->get('alias');
        $value = $request->get('value');
        if ($value && isset($config[$alias])) {
            $class = $config[$alias]['class'];
            $property = $config[$alias]['property'];
            /** @var EntityManagerInterface $em */
            $em = $this->getDoctrine()->getManagerForClass($class);
            $transformer = new EntityToIdTransformer($em, $class);
            //$value = $transformer->reverseTransform($value);
            foreach ($em->getRepository($class)->findBy([$property => $value]) as $item) {
                $data[$transformer->transform($item)] = (string) $item;
            }
        }

        return new JsonResponse($data);
    }
}
