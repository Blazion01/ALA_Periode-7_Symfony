<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ProfileRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use App\Form\WagFiltersType;

class ShowController extends AbstractController
{
    #[Route('/show', name: 'app_show')]
    public function index(EntityManagerInterface $entityManager, ProfileRepository $ProfileRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $user = null;
        $profile = null;
        if($this->getUser()) {
            $user = $this->getUser();
            if($user->getProfile()) {
                $profile = $user->getProfile();
            } else {
                return $this->redirectToRoute('app_profile_create');
            }
        }

        if(!$user) {
            $this->addFlash('error', 'User was not found');
            
            return $this->redirectToRoute('app_login');
        }
        $queryBuilder = $ProfileRepository->findAllExceptQuery($profile->getId());

        //$form = $this->createForm(WagFiltersType::class);
        //$form->handleRequest($request);

        if(isset($form) && $form->isSubmitted() && $form->isValid()) {
            if($form->get('country')->getData()) {
                $queryBuilder->addWhere('country = '.$form->get('country')->getData());
            }
            if($form->get('province')->getData()) {
                $queryBuilder->addWhere('province = '.$form->get('province')->getData());
            }
            if($form->get('city')->getData()) {
                $queryBuilder->addWhere('city = '.$form->get('city')->getData());
            }
        }

        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            25
        );

        return $this->render('show/index.html.twig', [
            'user' => $user,
            'pagination' => $pagination,
            'profile' => $profile,
            'profileImg' => null,
            'relativeBuildPath' => '.',
            'filters' => null /* $form->createView() */,
        ]);
    }
}
