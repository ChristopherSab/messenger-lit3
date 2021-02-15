<?php


namespace App\Controller;

use App\Service\MessageService;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Kreait\Firebase\Database;
use Kreait\Firebase\Storage;
use Kreait\Firebase\Messaging;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MessageController extends AbstractController
{
    private $database;
    private $storage;
    private $messageService;

    public function __construct(Database $database, Storage $storage, MessageService $messageService)
    {
        $this->database = $database;
        $this->storage = $storage;
        $this->messageService = $messageService;
    }


    /**
     *
     * @Route("/chat_home/new_message/{contact}", name="get_messages", methods="GET")
     * @IsGranted("ROLE_USER")
     * @param string $contact
     */
    public function getMessages(string $contact, EntityManagerInterface $em) : Response
    {

        $loggedInUser = $this->getUser()->getUsername();

        //Find User In DataBase
        $user = $em->getRepository(User::class)->findOneBy([
            'username' => $contact,
        ]);

        //Check If User Exists In Database
        if(!$user){
            return new Response('Unable To Find user', 400);
        }

        $userChat = $this->database->getReference('/userChats/'.$loggedInUser.'/'.$contact.'/');
        $conversationId = $userChat->getValue()['conversationId'];

        if(!$conversationId)
        {
            return new Response('', 204);
        }

        $messages = $this->messageService->returnFormattedMessages($conversationId, $loggedInUser, $contact);

        return $this->json($messages);

    }


    /**
     *
     * @Route("/chat_home/check_if_unread_messages/{loggedInUser}", name="get_unread_messages", methods="GET")
     * @IsGranted("ROLE_USER")
     * @param string $loggedInUser
     */
    public function getUnReadMessages(string $loggedInUser, EntityManagerInterface $em) : Response
    {
        //Find User In DataBase
        $user = $em->getRepository(User::class)->findOneBy([
            'username' => $loggedInUser,
        ]);

        //Check If User Exists In Database
        if(!$user){
            return new Response('Unable To Find user', 400);
        }

        $reference = $this->database->getReference('/userChats/'.$loggedInUser.'/');
        $conversations = $reference->getValue();

        return $this->json($conversations);

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

        $loggedInUser = $this->getUser()->getUsername();

       $form_Message = $request->request->get('chat_form')['message'];

       if($form_Message === ''){
           return new Response('You Cannot Send An Empty Message', 204);
       }

        // -------- Conversation Data -------- //
        $conversationId = $this->messageService->updateOrCreateNewConversation($loggedInUser, $contact);


        // -------- Message Data ------------- //
        $messageId = Uuid::uuid4();

        $message_content = [
            'messageId' => $messageId,
            'sender' => $loggedInUser,
            'receiver' => $contact,
            'message' => $form_Message,
            'time' => Database::SERVER_TIMESTAMP,
            'email_sent' => 'false',
            'read' => 'false'
        ];

        // -------- Attachments Data -------- //
        /** @var UploadedFile[] $attachments */
        $attachments = $request->files->get('chat_form')['attachment'];

        if($attachments){
            $message_content['attachments'] = $this->messageService->saveFilesToStorage($attachments, $conversationId, $messageId);
        }

        $this->database->getReference('messages/'.$conversationId.'/'.$messageId)
            ->set($message_content);

        // -------- User Chat Data ----------- //
        $this->messageService->saveUserChatData($loggedInUser, $contact, $conversationId);

        return new Response();

    }

}