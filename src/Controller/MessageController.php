<?php


namespace App\Controller;

use App\Service\MessageService;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Kreait\Firebase\Database;
use Kreait\Firebase\Storage;
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
     * @return Response
     * @param string $contact
     * @param EntityManagerInterface $em
     * @throws \Kreait\Firebase\Exception\DatabaseException
     * @Route("/chat_home/new_message/{contact}", name="get_messages", methods="GET")
     * @IsGranted("ROLE_USER")
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
     * @return Response
     * @param string $loggedInUser
     * @param EntityManagerInterface $em
     * @throws \Kreait\Firebase\Exception\DatabaseException
     * @Route("/chat_home/check_if_unread_messages/{loggedInUser}", name="get_unread_messages", methods="GET")
     * @IsGranted("ROLE_USER")
     *
     */
    public function getUnReadMessages(string $loggedInUser, EntityManagerInterface $em) : Response
    {

        $user = $em->getRepository(User::class)->findOneBy([
            'username' => $loggedInUser,
        ]);

        if(!$user){
            return new Response('Unable To Find user', 400);
        }

        $reference = $this->database->getReference('/userChats/'.$loggedInUser.'/');
        $conversations = $reference->getValue();

        return $this->json($conversations);

    }


    /**
     * @return Response
     * @param Request $request
     * @param string $contact
     * @throws \Kreait\Firebase\Exception\DatabaseExceptio
     * @Route("/chat_home/{contact}", name="post_message", methods="POST")
     * @IsGranted("ROLE_USER")
     *
     */
    public function postMessage(string $contact, Request $request): Response
    {

        $loggedInUser = $this->getUser()->getUsername();

       $form_Message = $request->request->get('chat_form')['message'];

       if($form_Message === ''){
           return new Response('You Cannot Send An Empty Message', 204);
       }

        // -------- Conversation Data -------- //
        $conversationId = $this->messageService->updateOrCreateNewConversation($loggedInUser, $contact);

        // -------- Create A New Message ID ------------- //
        $messageId = Uuid::uuid4();


        // -------- Save Attachments To Storage & Create Reference In RealTime Database -------- //
        /** @var UploadedFile[] $attachments */
        $attachments = $request->files->get('chat_form')['attachment'];

        if($attachments){
            $message_content['attachments'] = $this->messageService->saveFilesToStorage($attachments, $conversationId, $messageId);
        }

        // -------- Save Message Data-------- //
        $this->messageService->saveMessageData($conversationId, $messageId, $loggedInUser, $contact, $form_Message);

        // -------- User Chat Data ----------- //
        $this->messageService->saveUserChatData($loggedInUser, $contact, $conversationId);

        return new Response();

    }

}