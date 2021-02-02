<?php


namespace App\Controller;

use App\Form\ChatFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Kreait\Firebase\Database;

class MessageController extends AbstractController
{
    private $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }


    /**
     * @Route("/chat_session/{user}", name="chat_session")
     * @param Request $request
     */
    public function chat(Request $request, string $user)
    {



        return $this->render('base.html.twig', [
            'test' => 'test'
        ]);



    }



    /*public function conversation()
    {

        $reference = $this->database->getReference('/email');
        $snapshot = $reference->getSnapshot();
        $value = $snapshot->getValue();

        return $this->render('base.html.twig', [
            'test' => $value
        ]);

    }

    */

}