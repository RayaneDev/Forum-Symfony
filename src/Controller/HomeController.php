<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use App\Entity\Topic;
use App\Form\TopicType;
use App\Entity\Category;
use Cocur\Slugify\Slugify;
use App\Entity\TopicSearch;
use App\Form\TopicSearchType;
use App\Form\RegistrationType;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use App\Repository\TopicRepository;
use App\Repository\CategoryRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class HomeController extends AbstractController
{
    public function __construct(ObjectManager $manager, PostRepository $postRepo, UserRepository $userRepo, CategoryRepository $categoryRepo, TopicRepository $topicRepo) 
    {
        $this->manager = $manager; 
        $this->postRepo = $postRepo; 
        $this->userRepo = $userRepo; 
        $this->categoryRepo = $categoryRepo; 
        $this->topicRepo = $topicRepo; 

        $this->slugify = new Slugify(); 
        
    }


    /**
     * @Route("/", name="home_index")
     * @Route("/category/{slug}", name="home_category")
     */
    public function index(Category $category = null, Request $request, PaginatorInterface $paginator)
    {
        // On récupère les catégories ainsi que la catégorie sélectionnée

        $categories = $this->categoryRepo->findAll(); 

        $currCategory = ($category === null) ? $categories[0] : $category; 


        // Formulaire de la soumission d'un Topic

        $topic = new Topic(); 

        $formTopic = $this->createForm(TopicType::class, $topic); 

        $formTopic->handleRequest($request); 

        $user = $this->getUser(); 

        if ($formTopic->isSubmitted() && $formTopic->isValid() && !empty($user)) {

            if (!$topic->getId()) {
                $topic->setCreatedAt(new \DateTime()); 
            }
            $topic->setUser($user)
                  ->setCategory($currCategory)
                  ->setSlug($this->slugify->slugify($topic->getTitle()));


            $this->manager->persist($topic); 

            $this->manager->flush(); 

            return $this->redirectToRoute('topic_show', array(
                'slug' => $topic->getSlug(),
                'id' => $topic->getId()
            ));
        }

        // Formulaire de la soumission d'un TopicSearch 

        $topicSearch = new TopicSearch(); 

        $formTopicSearch = $this->createForm(TopicSearchType::class, $topicSearch); 

        $formTopicSearch->handleRequest($request); 

        $searchMode = False; 

        if ($formTopicSearch->isSubmitted() && $formTopicSearch->isValid()) {

            $searchMode = True; 

        }

        // On récupère tous les topics avec une pagination 

        if (!$searchMode) {
            $results = $this->topicRepo->findBy(['category' => $currCategory], array('id' => 'DESC')); 
        } else {
            
            // On récupère les topics recherchés selon la requête 

            $section = $topicSearch->getSection(); 

            // Sujet

            switch ($section) {
                case 'title': 
                    $results = $this->topicRepo->findBySubject($topicSearch->getResearch(), $currCategory); 
                    break; 
                case 'author': 
                    $results = $this->topicRepo->findByAuthor($topicSearch->getResearch(), $currCategory); 
                    break; 
                case 'message': 
                    $results = $this->topicRepo->findByMessage($topicSearch->getResearch(), $currCategory); 
                    break;
            }

        }

        $topics = $paginator->paginate(
            $results, 
            $request->query->getInt('page', 1), 
            20
        );



        return $this->render('home/index.html.twig', [
            'page' => 'home', 
            'categories' => $categories, 
            'currCategory' => $currCategory, 
            'topics' => $topics, 
            'formTopic' => $formTopic->createView(), 
            'formTopicSearch' => $formTopicSearch->createView()
        ]);
    }

    /**
     * @Route("/register", name="home_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $encoder)
    {
        $user = new User(); 

        $form = $this->createForm(RegistrationType::class, $user); 

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$user->getId()) {
                $user->setCreatedAt(new \DateTime()); 
            }

            $hash = $encoder->encodePassword($user, $user->getPassword()); 

            $user->setPassword($hash);

            $this->manager->persist($user); 
            $this->manager->flush($user); 

            $this->addFlash(
                'success',
                'Vous êtes bien inscrit !'
            );

            return $this->redirectToRoute('login'); 

        }

        return $this->render('home/register.html.twig', [
            'page' => 'register',
            'formRegister' => $form->createView()
        ]);
    }
}
