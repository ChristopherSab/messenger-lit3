<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Form\ChatFormType;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;

class ChatHomeController extends AbstractController
{
    /**
     * @Route("/chat_home/", name="chat_home", methods="GET")
     * 
     * @param EntityManagerInterface $em
     * 
     * @return Response
     */
    public function chatHomePage(EntityManagerInterface $em): Response
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
