<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ProfileRepository;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        $user = null;
        if($this->getUser()) {
            $user = $this->getUser()->getUsername();
        }

        return $this->render('default/index.html.twig', [
            'user' => $user,
            'controller_name' => 'DefaultController',
        ]);
    }
}
