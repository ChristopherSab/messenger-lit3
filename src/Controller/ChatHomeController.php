<?php


namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ChatHomeController extends AbstractController
{



    /**
     * @Route("/chat_home", name="chat_home")
     */
    public function chatHomePage(EntityManagerInterface $em)
    {

        $Repository = $em->getRepository(User::class);
        $users = $Repository->findAll();


        return $this->render('chat/index.html.twig', [
            'controller_name' => 'Chat Home',
            'users' => $users
        ]);

    }

}