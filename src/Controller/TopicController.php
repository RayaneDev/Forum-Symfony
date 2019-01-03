<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Topic;
use App\Form\PostType;
use App\Form\TopicType;
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
     * @Route("/topic/edit/{slug}/{id}", name="topic_edit")
     */
    public function index(Topic $topic, Request $request, PaginatorInterface $paginator)
    {
        $post = new Post(); 

        $formPost = null; 
        $formTopic = null; 

        $user = $this->getUser();

        $posts = $paginator->paginate(
            $this->postRepo->findBy([
                'topic' => $topic
            ], array('id' => 'ASC')), 
            $request->query->getInt('page', 1), 
            20
        );


        if (!empty($user)) {

            if ($request->attributes->get('_route') == 'topic_edit' && $user->getPseudo() == $topic->getUser()->getPseudo()) {
            
                $formTopic = $this->createForm(TopicType::class, $topic); 
    
                $formTopic->handleRequest($request); 
    
                if ($formTopic->isSubmitted() && $formTopic->isValid()) {
                    $topic->setTitle($topic->getTitle()); 
                    $topic->setContent($topic->getContent()); 
                    $topic->setEditedAt(new \DateTime()); 
    
                    $this->manager->persist($topic); 
                    $this->manager->flush(); 

                    return $this->redirectToRoute('topic_show', array(
                        'id' => $topic->getId(),
                        'slug' => $topic->getSlug(),
                        'page' => 1,
                        '_fragment' => 'top'
                    ));
                    
                }
            } else {
                $formPost = $this->createForm(PostType::class, $post); 
    
                $formPost->handleRequest($request); 
    
                if ($formPost->isSubmitted() && $formPost->isValid()) {
                
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
            }
        }

        return $this->render('topic/show.html.twig', [
            'page' => 'topic',
            'topic' => $topic, 
            'posts' => $posts,
            'formPost' => (($formPost !== null) ? $formPost->createView() : null), 
            'formTopic' => (($formTopic !== null) ? $formTopic->createView() : null)
        ]);
    }

    /**
     * @Route("/topic/remove/{id}/token/{token}", name="topic_remove")
     */
    public function remove (Topic $topic, Request $request) 
    {
        
        if ($this->getUser()->getPseudo() == $topic->getUser()->getPseudo()) {

        
           

            $token = $request->attributes->get('token');

            if ($this->isCsrfTokenValid('delete-item', $token)) {
                $this->manager->remove($topic); 
                $this->manager->flush(); 

                return $this->redirectToRoute('home_index');

            }
        }

        return $this->redirectToRoute('topic_show', [
            'id' => $topic->getId(), 
            'slug' => $topic->getSlug()
        ]);
    }
}
