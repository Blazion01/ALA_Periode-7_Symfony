<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ProfileRepository;
use App\Repository\DogRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use App\Form\WagFiltersType;

class ShowController extends AbstractController
{
    #[Route('/show', name: 'app_show')]
    public function show(EntityManagerInterface $entityManager, ProfileRepository $ProfileRepository, PaginatorInterface $paginator, Request $request): Response
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

    #[Route('/show/dog/{slug}', name: 'app_show_dog')]
    /*
     * @param $slug
     */
    public function showDog($slug = null, DogRepository $DogRepository, Request $request): Response
    {
        if(!is_numeric($slug) || !$DogRepository->find($slug)) {
            if(!$DogRepository->find($slug)) {
                $this->addFlash('info', 'Dog with id \''.$slug.'\' not found.');
            }
            $this->redirectToRoute('app_show');
        } else {
            $showDog = $DogRepository->find($slug);
        }

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

        return $this->render('show/dog.html.twig', [
            'user' => $user,
            'profile' => $profile,
            'showDog' => $showDog,
            'profileImg' => null,
            'relativeBuildPath' => '../../',
        ]);
    }

    #[Route('/show/{slug}', name: 'app_show_profile')]
    /*
     * @param $slug
     */
    public function showProfile($slug = null, ProfileRepository $ProfileRepository, Request $request): Response
    {
        if(!is_numeric($slug) || !$ProfileRepository->find($slug)) {
            if(!$ProfileRepository->find($slug)) {
                $this->addFlash('info', 'Profile with id \''.$slug.'\' not found.');
            }
            $this->redirectToRoute('app_show');
        } else {
            $showProfile = $ProfileRepository->find($slug);
        }

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

        return $this->render('show/profile.html.twig', [
            'user' => $user,
            'profile' => $profile,
            'showProfile' => $showProfile,
            'profileImg' => null,
            'relativeBuildPath' => '../',
        ]);
    }
}
