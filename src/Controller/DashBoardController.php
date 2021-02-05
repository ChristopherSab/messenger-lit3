<?php


namespace App\Controller;


use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DashBoardController extends AbstractController
{

    /**
     * @Route("/dashboard", name="chat_dashboard")
     */
    public function UserDashBoard(EntityManagerInterface $em)
    {

        $Repository = $em->getRepository(User::class);
        $users = $Repository->findAll();


        return $this->render('dashboard/dashboard.html.twig', [
            'controller_name' => 'chat_dashboard',
            'users' => $users
        ]);

    }

}