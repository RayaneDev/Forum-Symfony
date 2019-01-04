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
     * @Route("/post/edit/{id}", name="post_edit")
     */
    public function index(Topic $topic = null, Request $request, PaginatorInterface $paginator)
    {
        $editPostMode = ($request->attributes->get('_route') == 'post_edit');
        $editTopicMode = ($request->attributes->get('_route') == 'topic_edit');

        $user = $this->getUser(); 
        $form = null; // Form utilisé pour l'édition d'un topic et d'un post et la soumission d'un post

        if ($editPostMode) {
            $post = $this->postRepo->findOneBy([
                'id' => $request->attributes->get('id')
            ]); 
            $topic = $post->getTopic(); 
        } else {
            $post = new Post(); 
        }

        $posts = $paginator->paginate(
            $this->postRepo->findBy([
                'topic' => $topic
            ], array('id' => 'ASC')), 
            $request->query->getInt('page', 1), 
            20
        );

        if (empty($user)) {
            if ($editPostMode || $editTopicMode) {
                return $this->redirectToRoute('home_index');
            }
        } else {

            if ($editTopicMode) {
    
                if (!empty($topic) && !empty($this->getUser()) && $user == $topic->getUser()) {
                    // On génère le formulaire d'édition de topic 
                    $form = $this->createForm(TopicType::class, $topic); 

                    $form->handleRequest($request); 

                    if ($form->isSubmitted() && $form->isValid()) {

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
                }
    
            } else {

                if ($editPostMode) {

                    $page = (empty($request->query->get('page'))) ? 1 : $request->query->get('page'); 
        
                    $topic = $post->getTopic(); 
                    $fragment = $post->getId();
                    
                } else {

                    $page = ($posts->getPageCount() > 0) ? $posts->getPageCount() : 1; 
                    $fragment = 'bottom'; 
                }


                $form = $this->createForm(PostType::class, $post); 
        
                $form->handleRequest($request); 
            
                if ($form->isSubmitted() && $form->isValid()) {
                        
                    if (!$post->getId()) {
    
                        $post->setCreatedAt(new \DateTime()); 
    
                        $post->setUser($user); 
                        $post->setTopic($topic); 
    
                        $this->manager->persist($post); 
                    } else {
                        if ($user == $post->getUser()) {
                            $post->setEditedAt(new \Datetime());
                            $this->manager->persist($post); 
                        }
                    }
    
                    $this->manager->flush(); 
  
                    return $this->redirectToRoute('topic_show', array(
                        'id' => $topic->getId(),
                        'slug' => $topic->getSlug(),
                        'page' => $page, 
                        '_fragment' => $fragment
                    ));
                }
            }
        }

        return $this->render('topic/show.html.twig', [
            'page' => 'topic',
            'topic' => $topic, 
            'posts' => $posts,
            'form' => ($form != null) ? $form->createView() : null,
            'editPostMode' => $editPostMode, 
            'editTopicMode' => $editTopicMode
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
