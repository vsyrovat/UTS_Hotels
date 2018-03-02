<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function index()
    {
        return $this->render('Default/index.html.twig');
    }
    /**
     * @Route("/prepare", name="prepare")
     */
    public function prepare()
    {
        return $this->render('Default/prepare.html.twig');
    }
    /**
     * @Route("/task", name="task")
     */
    public function task()
    {
        return $this->render('Default/task.html.twig');
    }
}
