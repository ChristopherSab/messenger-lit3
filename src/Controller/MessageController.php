<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\MessageService;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Kreait\Firebase\Database;
use Kreait\Firebase\Storage;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MessageController extends AbstractController
{
    private const EMPTY_REQUEST_CODE = 204;
    private const BAD_REQUEST_CODE = 400;
    private const NO_USER_FOUND_ERROR = 'Unable To Find user';
    private const EMPTY_MESSAGE_ERROR = 'You Cannot Send An Empty Message';

    /**
     * @var Database
     */
    private $database;

    /**
     * @var Storage
     */
    private $storage;

    /**
     * @var MessageService
     */
    private $messageService;

    /**
     * @param Database $database
     * @param Storage $storage
     * @param MessageService $messageService
     */
    public function __construct(
        Database $database, 
        Storage $storage, 
        MessageService $messageService
    ) {
        $this->database = $database;
        $this->storage = $storage;
        $this->messageService = $messageService;
    }

    /**
     * @Route("/chat_home/new_message/{contact}", name="get_messages", methods="GET")
     * 
     * @param string $contact
     * @param EntityManagerInterface $em
     * 
     * @throws \Kreait\Firebase\Exception\DatabaseException
     * @return Response
     */
    public function getMessages(string $contact, EntityManagerInterface $em) : Response
    {
        $loggedInUser = $this->getUser()->getUsername();

        $user = $em->getRepository(User::class)->findOneBy([
            'username' => $contact,
        ]);

        if (!$user) {
            return new Response(self::NO_USER_FOUND_ERROR, self::BAD_REQUEST_CODE);
        }

        $userChat = $this->database->getReference('/userChats/'.$loggedInUser.'/'.$contact.'/');
        $conversationId = $userChat->getValue()['conversationId'];

        if (!$conversationId) {
            return new Response('', self::EMPTY_MESSAGE_ERROR);
        }

        $messages = $this->messageService->returnFormattedMessages(
            $conversationId, 
            $loggedInUser, 
            $contact
        );

        return $this->json($messages);
    }


    /**
     * @Route("/chat_home/check_if_unread_messages/{loggedInUser}", name="get_unread_messages", methods="GET")
     * 
     * @param string $loggedInUser
     * @param EntityManagerInterface $em
     * 
     * @throws \Kreait\Firebase\Exception\DatabaseException
     * 
     * @return Response
     */
    public function getUnReadMessages(string $loggedInUser, EntityManagerInterface $em) : Response
    {
        $user = $em->getRepository(User::class)->findOneBy([
            'username' => $loggedInUser,
        ]);

        if(!$user){
            return new Response(self::NO_USER_FOUND_ERROR, 400);
        }

        $reference = $this->database->getReference('/userChats/'.$loggedInUser.'/');
        $conversations = $reference->getValue();

        return $this->json($conversations);

    }

    /**
     * @Route("/chat_home/{contact}", name="post_message", methods="POST")
     * @param Request $request
     * @param string $contact
     * 
     * @throws \Kreait\Firebase\Exception\DatabaseException
     * 
     * @return Response
     */
    public function postMessage(string $contact, Request $request): Response
    {
        $loggedInUser = $this->getUser()->getUsername();

        $form_Message = $request->request->get('chat_form')['message'];

        if ($form_Message === '') {
            return new Response(self::EMPTY_MESSAGE_ERROR, self::EMPTY_REQUEST_CODE);
        }

        $conversationId = $this->messageService->updateOrCreateNewConversation($loggedInUser, $contact);

        $messageId = Uuid::uuid4();

        /** @var UploadedFile[] $attachments */
        $attachments = $request->files->get('chat_form')['attachment'];

        if ($attachments) {
            $message_content['attachments'] = $this->messageService->saveFilesToStorage(
                $attachments, 
                $conversationId, 
                $messageId
            );
        }

        $this->messageService->saveMessageData(
            $conversationId, 
            $messageId, 
            $loggedInUser, 
            $contact, 
            $form_Message
        );

        $this->messageService->saveUserChatData(
            $loggedInUser, 
            $contact, 
            $conversationId
        );

        return new Response();

    }
}
