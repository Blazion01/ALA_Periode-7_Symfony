<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ProfileRepository;
use App\Repository\FlagRepository;
use App\Repository\UserRepository;
use App\Repository\DogRepository;
use Doctrine\ORM\EntityManagerInterface;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(ProfileRepository $ProfileRepository, FlagRepository $FlagRepository): Response
    {
        $user = null;
        $profile = null;
        $profileCount = 0;
        if($this->getUser()) {
            $user = $this->getUser();
            if($ProfileRepository->findOneBy(['User' => $user->getId()])) {
                $profile = $ProfileRepository->findOneBy(['User' => $user->getId()]);
                $profileCount += -1;
            } else {
                return $this->redirectToRoute('app_profile_create');
            }
        }

        $profileCount += count($ProfileRepository->findAll());
        $allFlags = $FlagRepository->findAll();
        $matchedFlags = [];
        $matchCount = 0;
        foreach ($allFlags as $rowId => $row) {
            if (!in_array($row['id'], $matchedFlags) ) {
                for ($i=0; $i < count($allFlags); $i++) {
                    if (
                        $row['id'] != $allFlags[$i]['id'] &&
                        $row['Flagger'] == $allFlags[$i]['Flagged'] &&
                        $row['Flagged'] == $allFlags[$i]['Flagger'] &&
                        $row['Interested'] == 'Yes' &&
                        $allFlags[$i]['Interested'] == 'Yes'
                    ) {
                        $matchedFlags->array_push($row['id']);
                        $matchedFlags->array_push($allFlags[$i]['id']);
                        $matchCount++;
                    }
                }
            }
        }

        return $this->render('default/index.html.twig', [
            'user' => $user,
            'profiles' => $profileCount,
            'profile' => $profile,
            'profileImg' => null,
            'matches' => $matchCount,
            'relativeBuildPath' => '.',
        ]);
    }

    #[Route('/{entity}/remove/{slug}', name: 'app_remove')]
    /*
     * @param $entity
     * @param $slug
     */
    public function dogRemove($entity, $slug, EntityManagerInterface $entityManager, DogRepository $DogRepository, ProfileRepository $ProfileRepository, UserRepository $UserRepository, FlagRepository $FlagRepository, Request $request): Response
    {
        $user = null;
        $profile = null;
        $update = null;
        if($this->getUser()) {
            $user = $this->getUser();
            if($user->getProfile()) {
                $profile = $user->getProfile();
            }
        }

        if(!$user) {
            $this->addFlash('error', 'User was not found');
            
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(RemoveFormType::class);

        switch ($entity) {
            case 'dog':
                if(is_numeric($slug) && $slug != 0 && $DogRepository->find($slug)) {
                    $dog = $DogRepository->find($slug);
                }
                if(isset($dog) && $profile != $dog->getProfile()) {
                    $this->addFlash('info', `You are not the owner of dog number: $slug`);
        
                    return $this->redirectToRoute('app_profile');
                } elseif(!isset($dog)) {
                    $this->addFlash('error', `There is no dog number: $slug`);
        
                    return $this->redirectToRoute('index');
                }

                $form->handleRequest($request);
        
                if($form->isSubmitted() && $form->isValid()) {
                    if($form->get('Remove')->getData() == true) {
                        $profile->removeDog($dog);
                        $entityManager->flush();
                        $this->addFlash('succes', 'Dog removed');
                        return $this->redirectToRoute('app_profile');
                    } else {
                        return $this->redirectToRoute('app_profile');
                    }
                }
                break;
            case 'user':
                $this->addFlash('error', 'UserEntityType removal not allowed');
                return $this->redirectToRoute('index');
                break;
            case 'profile':
                $this->addFlash('error', 'ProfileEntityType removal not allowed');
                return $this->redirectToRoute('index');
                break;
            case 'flag':
                $this->addFlash('error', 'FlagEntityType removal not allowed');
                return $this->redirectToRoute('index');
                break;
            default:
                $this->addFlash('error', 'EntityType not found');
                return $this->redirectToRoute('index');
                break;
        }

        return $this->render('default/remove.html.twig', [
            'removeForm' => $form->createView(),
            'user' => $user,
            'profile' => $profile,
            'profileImg' => null,
            'relativeBuildPath' => '../..',
            'entityType' => 'Dog',
        ]);
    }
}
