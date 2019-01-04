<?php

namespace App\Controller;

use App\Entity\Post;
use App\Repository\PostRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PostController extends AbstractController
{

    public function __construct (ObjectManager $manager, PostRepository $postRepo) {
        $this->manager = $manager; 
        $this->postRepo = $postRepo; 
    }

    /**
     * @Route("/post/remove/{id}/{token}", name="post_remove")
     */
    public function remove(Post $post, Request $request)
    {
        $user = $this->getUser(); 
        $token = $request->attributes->get('token'); 

        if (!empty($user) && $user == $post->getUser() && $this->isCsrfTokenValid('delete-post', $token)) {

            $topic = $post->getTopic(); 

            $this->manager->remove($post); 
            $this->manager->flush(); 

            return $this->redirectToRoute('topic_show', [
                'id' => $topic->getId(),
                'slug' => $topic->getSlug(), 
                'page' => $request->query->get('page')
            ]); 
        }

        return $this->redirectToRoute('home_index');
    }
}
