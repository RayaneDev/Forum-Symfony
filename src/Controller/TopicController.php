<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Topic;
use App\Form\PostType;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use App\Repository\TopicRepository;
use App\Repository\CategoryRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TopicController extends AbstractController
{
    public function __construct(ObjectManager $manager, PostRepository $postRepo, UserRepository $userRepo, CategoryRepository $categoryRepo, TopicRepository $topicRepo) 
    {
        $this->manager = $manager; 
        $this->postRepo = $postRepo; 
        $this->userRepo = $userRepo; 
        $this->categoryRepo = $categoryRepo; 
        $this->topicRepo = $topicRepo; 
    }

    
    /**
     * @Route("/topic/{slug}/{id}", name="topic_show")
     */
    public function topic(Topic $topic, Request $request, PaginatorInterface $paginator)
    {
        $post = new Post(); 
        $form = $this->createForm(PostType::class, $post); 

        $form->handleRequest($request); 

        $user = $this->getUser();
        
        $posts = $paginator->paginate(
            $this->postRepo->findBy([
                'topic' => $topic
            ], array('id' => 'DESC')), 
            $request->query->getInt('page', 1), 
            9
        );


        if ($form->isSubmitted() && $form->isValid() && !empty($user)) {
            
            if (!$post->getId()) {
                $post->setCreatedAt(new \DateTime()); 
            }

            $post->setUser($user); 
            $post->setTopic($topic); 

            $this->manager->persist($post); 
            $this->manager->flush(); 

            return $this->redirectToRoute('topic_show', array(
                'id' => $topic->getId(),
                'slug' => $topic->getSlug(),
                'page' => $posts->getPageCount(),
                '_fragment' => 'lastPost'
            ));
        }

        return $this->render('topic/show.html.twig', [
            'page' => 'topic',
            'topic' => $topic, 
            'posts' => $posts,
            'formPost' => $form->createView()
        ]);
    }
}
