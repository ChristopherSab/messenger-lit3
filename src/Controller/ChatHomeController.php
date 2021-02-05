<?php


namespace App\Controller;

use App\Entity\User;
use App\Form\ChatFormType;
use Doctrine\ORM\EntityManagerInterface;
use Kreait\Firebase\Database;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
     * @Route("/chat_home/", name="chat_home", methods="GET")
     * @IsGranted("ROLE_USER")
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


    /**
     *
     * @Route("/chat_home/new_message/{contact}", name="get_messages", methods="GET")
     * @IsGranted("ROLE_USER")
     * @param string $contact
     */
    public function getMessages(string $contact, EntityManagerInterface $em) : Response
    {

        //This is the current Logged in User
        $loggedInUser = $this->getUser()->getUsername();

        //Find User In DataBase
        $Repository = $em->getRepository(User::class);

        $user = $Repository->findOneBy([
            'username' => $contact,
        ]);

        //Check If User Exists In Database, Otherwise Erro
        if(!$user){
            return new Response('Unable To Find user', 400);
        }


        $reference = $this->database->getReference('/userChats/'.$loggedInUser.'/'.$contact.'/');

        $conversationID = $reference->getValue()['conversationID'];

        if(!$conversationID)
        {
            return new Response('', 204);
        }


        $reference = $this->database->getReference('messages/'.$conversationID.'/' )->orderByChild('time');
        $messages = $reference->getValue();

        return $this->json($messages);

    }



    /**
     *
     * @Route("/chat_home/{contact}", name="post_message", methods="POST")
     * @IsGranted("ROLE_USER")
     * @param Request $request
     * @param string $contact
     */
    public function postMessage(string $contact, Request $request)
    {

        //This is the current Logged in User
        $loggedInUser = $this->getUser()->getUsername();

       $form_Message = $request->request->get('chat_form')['message'];


        // -------- Conversation Data -------- //
        $conversationReference = $this->database->getReference('userChats/'.$loggedInUser.'/'.$contact.'/');

        $conversationID = $conversationReference->getValue()['conversationID'];

        if(!$conversationID)
        {
            $conversationID = Uuid::uuid4();
            $conversationReference->set(["conversationID" => $conversationID]);

            $conversationReference2 = $this->database->getReference('userChats/'.$contact.'/'.$loggedInUser.'/');

            $conversationReference2->set(["conversationID" => $conversationID]);


            $this->database->getReference('conversations/'.$conversationID.'/')
            ->set(["conversationID" => $conversationID]);

        }

        // -------- Message Data ------------- //
        $message_content = [
            'sender' => $loggedInUser,
            'message' => $form_Message,
            'time' => Database::SERVER_TIMESTAMP,
            'email_sent' => 'Boolean',
            'read' => 'Boolean',
        ];

        $this->database->getReference('messages/'.$conversationID)
            ->push($message_content);


        // -------- User Chat Data ----------- //
        $this->database->getReference('userChats/'.$loggedInUser.'/'.$contact.'/')
            ->update([
                'conversationID' => $conversationID,
                'Read' => 'Boolean Value',

            ]);

        return new Response();

    }


}