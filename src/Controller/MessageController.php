<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\Message;
use App\Form\AnswerType;
use App\Form\MessageType;
use App\Repository\UserRepository;
use App\Repository\AnswerRepository;
use App\Repository\MessageRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MessageController extends AbstractController
{

    public function __construct (ObjectManager $manager, MessageRepository $msgRepo, UserRepository $userRepo, AnswerRepository $answerRepo) {
        $this->manager = $manager; 
        $this->msgRepo = $msgRepo; 
        $this->userRepo = $userRepo; 
        $this->answerRepo = $answerRepo; 
    }



    /**
     * @Route("/messagerie", name="message_index")
     */
    public function index(Request $request)
    {

        $user = $this->getUser(); 

        if (empty($user)) {
            return $this->redirectToRoute('home_index'); 
        }
    
        $messages = $user->getMessagesReceived(); 

        $message = new Message(); 

        $form = $this->createForm(MessageType::class, $message); 

        $form->handleRequest($request); 

        if ($form->isSubmitted() && $form->isValid()) {

            $message->setAuthor($user); 

            $dest = $this->userRepo->findOneBy([
                'pseudo' => $message->getDestsPseudos()
            ]); 


            $message->addDest($dest); 

            $message->setCreatedAt(new \DateTime()); 

            $this->manager->persist($message); 
            $this->manager->flush(); 

            return $this->redirectToRoute('message_index', [
                'page' => 'messagerie', 
                'messages' => $messages
            ]); 

        }

        return $this->render('message/index.html.twig', [
            'page' => 'messagerie', 
            'messages' => $messages, 
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/message/{id}", name="message_show")
     */
    public function show(Message $message, Request $request, PaginatorInterface $paginator)
    {
        $user = $this->getUser(); 

        if (empty($user) || ($message->getAuthor() != $user && !in_array($user, $message->getDest()->toArray()))) {
            return $this->redirectToRoute("home_index"); 
        }

        $answers = $paginator->paginate(
            $this->answerRepo->findBy([
                'message' => $message
            ]),
            $request->query->getInt('page', 1),
            3
        );

        $answer = new Answer(); 

        $form = $this->createForm(AnswerType::class, $answer); 

        $form->handleRequest($request); 

        if ($form->isSubmitted() && $form->isValid()) {

            $answer->setCreatedAt(new \DateTime()); 
            $answer->setMessage($message); 
            $answer->setAuthor($user); 

            $this->manager->persist($answer);
            $this->manager->flush(); 

            return $this->redirectToRoute('message_show', [
                'id' => $message->getId()
            ]);
        }

        return $this->render('message/show.html.twig', [
            'page' => 'messagerie',
            'message' => $message, 
            'answers' => $answers, 
            'form' => $form->createView()
        ]);
    }
}
