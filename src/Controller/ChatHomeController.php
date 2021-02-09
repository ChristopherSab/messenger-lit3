<?php


namespace App\Controller;

use App\Entity\User;
use App\Form\ChatFormType;
use Doctrine\ORM\EntityManagerInterface;
use Kreait\Firebase\Database;
use Kreait\Firebase\Storage;
use Kreait\Firebase\Messaging;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ChatHomeController extends AbstractController
{

    private $database;

    private $storage;

    public function __construct(Database $database, Storage $storage)
    {
        $this->database = $database;
        $this->storage = $storage;
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

        //Check If User Exists In Database
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


        // -------- Loop Through All The Conversation Messages, If attachment/s exist to create a downloadable link -------- //
        foreach ($messages as &$messageId) {


            if (array_key_exists('attachments', $messageId)) {


                foreach ($messageId['attachments'] as $key => $fileId) {


                    $messageId['attachments'][$key]['signedUrl'] = '';

                    $disposition = HeaderUtils::makeDisposition('attachment', $fileId['originalFileName']);

                    $messageId['attachments'][$key]['signedUrl'] = $this->storage->getBucket()->object('Attachments/'.$conversationID.'/'.$messageId['messageId'].'/'.$key)


                        ->signedUrl(time() + 3600, [
                            'responseDisposition' => $disposition
                        ]);

                }

            }

        }


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

       if($form_Message === ''){
           return new Response('You Cannot Send An Empty Message', 204);
       }


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

        $messageId = Uuid::uuid4();

        $message_content = [
            'messageId' => $messageId,
            'sender' => $loggedInUser,
            'message' => $form_Message,
            'time' => Database::SERVER_TIMESTAMP,
            'email_sent' => 'Boolean',
            'read' => 'Boolean',
            //'attachments' => []
        ];


        // -------- Attachments Data -------- //
        /** @var UploadedFile[] $attachments */
        $attachments = $request->files->get('chat_form')['attachment'];

        $storageBucket = $this->storage->getBucket();


        if($attachments){

            foreach($attachments as $file){


                $fileId = Uuid::uuid4();

                $storageBucket->upload(file_get_contents($file->getPathname()), [
                    'name' => 'Attachments/'.$conversationID.'/'.$messageId.'/'.$fileId
                ]);

                $fileData = [
                    'originalFileName' => $file->getClientOriginalName(),
                    'fileType' => $file->getMimeType()

                ];

                $message_content['attachments'][$fileId->toString()] = $fileData;

            }
        }

        $this->database->getReference('messages/'.$conversationID.'/'.$messageId)
            ->set($message_content);


        // -------- User Chat Data ----------- //
        $this->database->getReference('userChats/'.$loggedInUser.'/'.$contact.'/')
            ->update([
                'conversationID' => $conversationID,
                'Read' => 'Boolean Value'
            ]);

        return new Response();

    }



}