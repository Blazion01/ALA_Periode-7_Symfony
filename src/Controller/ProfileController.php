<?php

namespace App\Controller;

use App\Entity\Profile;
use App\Entity\Flag;
use App\Entity\Dog;
use App\Form\ProfileFormType;
use App\Form\DogFormType;
use App\Repository\ProfileRepository;
use App\Repository\FlagRepository;
use App\Repository\DogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function index(ProfileRepository $ProfileRepository): Response
    {
        $user = null;
        $profile = null;
        if($this->getUser()) {
            $user = $this->getUser();
            if($ProfileRepository->findOneBy(['User' => $user->getId()])) {
                $profile = $ProfileRepository->findOneBy(['User' => $user->getId()]);
            } else {
                return $this->redirectToRoute('app_profile_create');
            }
        }

        return $this->render('profile/index.html.twig', [
            'profile' => 'ProfileController',
            'profile' => $profile,
            'profileImg' => null,
            'user' => $user,
            'relativeBuildPath' => '.',
        ]);
    }
    
    #[Route('/profile/create', name: 'app_profile_create')]
    #[Route('/profile/update', name: 'app_profile_update')]
    public function profileCreate(SluggerInterface $slugger, ProfileRepository $ProfileRepository, EntityManagerInterface $entityManager, Request $request): Response
    {
        $user = null;
        $profile = null;
        $update = null;
        $profileImage = null;
        if($this->getUser()) {
            $user = $this->getUser();
        }
        if($ProfileRepository->findOneBy(['User' => $user->getId()])) {
            $profile = $ProfileRepository->findOneBy(['User' => $user->getId()]);
            if($profile->getImageName()) {
                $profileImage = $profile->getImageName();
                $profile->setImageName($this->getParameter('upload_directory').'/'.$profileImage);
            }
            $update = 1;
        } else {
            $profile = new Profile();
            $profile->setUser($user);
        }

        if(!$user) {
            $this->addFlash('error', 'User was not found');
            
            return $this->redirectToRoute('app_login');
        }
        $form = $this->createForm(ProfileFormType::class, $profile);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            if(!in_array('ROLE_HAS_PROFILE', $user->getRoles())) {
                $user->setRoles(['ROLE_HAS_PROFILE']);
                $entityManager->persist($user);
            }

            $entityManager->persist($profile);
            $entityManager->flush();

            $pic_upload = $form->get('imageName')->getData();

            if($pic_upload) {
                if(!isset($profileImage)) {
                    $originalFilename = pathinfo($pic_upload->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $user->getUsername().'-'.$safeFilename.'-'.uniqid().'.'.$pic_upload->guessExtension();
                } else {
                    $newFilename = $profileImage;
                }

                try {
                    $pic_upload->move(
                        $this->getParameter('upload_directory'),
                        $newFilename
                    );
                    $profile->setImageName($newFilename);
                    $this->addFlash('success', 'Profile image updated successfully');
                } catch (FileException $e) {
                    $this->addFlash('error', 'Something went wrong while uploading the image. Please try again.');

                    return $this->redirectToRoute('app_profile_update');
                }
            } else {
                $profile->setImageName($profileImage);
            }

            $entityManager->persist($profile);
            $entityManager->flush();

            if ($update) {
                $this->addFlash('success', 'profile updated successfully');
            } else {
                $this->addFlash('success', 'profile created successfully');
                $this->addFlash('success', 'Your roles have been altered. If kicked out: log in again to gain access.');
            }
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/profile_create.html.twig', [
            'profileForm' => $form->createView(),
            'user' => $user,
            'profile' => $profile,
            'profileImg' => $profileImage,
            'relativeBuildPath' => '..',
            'update' => $update,
        ]);
    }
    
    #[Route('/dog/create', name: 'app_dog_create')]
    #[Route('/dog/update/{slug}', name: 'app_dog_update')]
    /*
     * @param $slug
     */
    public function dogCreate($slug = null, SluggerInterface $slugger, EntityManagerInterface $entityManager, DogRepository $DogRepository, Request $request): Response
    {
        $user = null;
        $profile = null;
        $dogImage = null;
        $update = null;
        if($this->getUser()) {
            $user = $this->getUser();
            if($user->getProfile()) {
                $profile = $user->getProfile();
                if(is_numeric($slug) && $DogRepository->find($slug)) {
                    $temp_dog = $DogRepository->find($slug);
                    foreach ($profile->getDogs() as $key => $value) {
                        if(isset($temp_dog) && $temp_dog == $value) {
                            $dog = $temp_dog;
                            if($dog->getImageName()) {
                                $dogImage = $dog->getImageName();
                                $dog->setImageName($this->getParameter('dog_directory').'/'.$dogImage);
                            }
                            $update = 1;
                        } elseif (!$update) {
                            if(isset($temp_dog) && $profile != $temp_dog->getProfile()) {
                                $this->addFlash('info', `You are not the owner of dog number: $slug`);
            
                                return $this->redirectToRoute('app_profile');
                            } elseif ($slug == 0) {
                                $dog = new Dog();
                                $dog->setProfile($profile);
                            }
                        }
                    }
                } else {
                    $dog = new Dog();
                    $dog->setProfile($profile);
                }
            }
        }

        if(!$user) {
            $this->addFlash('error', 'User was not found');
            
            return $this->redirectToRoute('app_login');
        }
        $form = $this->createForm(DogFormType::class, $dog);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            // $dog->setProfile($user->getProfile());

            $pic_upload = $form->get('imageName')->getData();

            if($pic_upload) {
                if(!isset($dogImage)) {
                    $originalFilename = pathinfo($pic_upload->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $user->getUsername().'-'.$safeFilename.'-'.uniqid().'.'.$pic_upload->guessExtension();
                } else {
                    $newFilename = $dogImage;
                }

                try {
                    $pic_upload->move(
                        $this->getParameter('dog_directory'),
                        $newFilename
                    );
                    $dog->setImageName($newFilename);
                    $this->addFlash('success', 'Dog image updated successfully');
                } catch (FileException $e) {
                    $this->addFlash('error', 'Something went wrong while uploading the image. Please try again.');

                    return $this->redirectToRoute('app_profile');
                }
            } else {
                $dog->setImageName($dogImage);
            }
            try {
                $entityManager->persist($dog);
                $entityManager->flush();
                $profile->addDog($dog);
            } catch (ORMException $e) {
                $this->addFlash('error', 'Something went wrong while inserting the dog. Please try again.');
                return $this->redirectToRoute('app_profile');
            }

            if ($update) {
                $this->addFlash('success', 'dog updated successfully');
            } else {
                $this->addFlash('success', 'dog created successfully');
            }
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/dog_create.html.twig', [
            'dogForm' => $form->createView(),
            'user' => $user,
            'profile' => $profile,
            'profileImg' => null,
            'dogImg' => $dogImage,
            'relativeBuildPath' => '../..',
            'update' => $update,
        ]);
    }
}
