<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use App\Entity\Topic;
use App\Form\PostType;
use App\Form\ProfilType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{

    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager; 
    }

    /**
     * @Route("/profile/{pseudo}", name="user_show")
     */
    public function profile(User $user, Request $request)
    {

        $form = $this->createForm(ProfilType::class, $user); 

        $form->handleRequest($request); 

        if ($form->isSubmitted() && $form->isValid()) {

            $this->manager->persist($user); 

            $this->manager->flush(); 

        }

    
        return $this->render('user/profile.html.twig', [
            'page' => 'profile',
            'user' => $user, 
            'formProfil' => $form->createView() 
        ]);
    }

}
