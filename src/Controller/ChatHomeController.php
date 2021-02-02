<?php


namespace App\Controller;

use App\Entity\User;
use App\Form\ChatFormType;
use Doctrine\ORM\EntityManagerInterface;
use Kreait\Firebase\Database;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ChatHomeController extends AbstractController
{

    private $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }


    /**
     * @Route("/chat_home", name="chat_home")
     */
    public function chatHomePage(EntityManagerInterface $em)
    {

        $form = $this->createForm(ChatFormType::class);

        $Repository = $em->getRepository(User::class);
        $users = $Repository->findAll();


        return $this->render('chat/index.html.twig', [
            'chatForm' => $form->createView(),
            'controller_name' => 'Chat Home',
            'users' => $users
        ]);

    }

}