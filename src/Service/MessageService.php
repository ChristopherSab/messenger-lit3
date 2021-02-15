<?php


namespace App\Service;


use Kreait\Firebase\Database;
use Kreait\Firebase\Storage;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;

class MessageService
{

    /** @var Database */
    private $database;

    /** @var Storage */
    private $storage;

    public function __construct(Database $database, Storage $storage)
    {
        $this->database = $database;
        $this->storage = $storage;
    }


    public function returnFormattedMessages( string $conversationId, string $loggedInUser, string $contact): array
    {

        $reference = $this->database->getReference('messages/'.$conversationId.'/' )->orderByChild('time');
        $messages = $reference->getValue();

        if($messages) {

            foreach ($messages as $messagesKey => $messageId ) {

                // - If a user makes a get request to receive messages, it changes the message read to true & Conversation to true -//
                if($messages[$messagesKey]['read'] === 'false' && $messages[$messagesKey]['sender'] === $contact){

                    $databaseMessages = $this->database->getReference('messages/'.$conversationId.'/'.$messagesKey);
                    $databaseMessages->update(['read' => 'true']);

                    $senderUserChat = $this->database->getReference('/userChats/'.$loggedInUser.'/'.$contact.'/');
                    $senderUserChat->update(['read' => 'true']);

                    $receiverUserChat = $this->database->getReference('/userChats/'.$contact.'/'.$loggedInUser.'/');
                    $receiverUserChat->update(['read' => 'true']);

                }


                // - Loop Through All The Conversation Messages, If attachment/s exist to create a downloadable link - //
                if (array_key_exists('attachments', $messageId)) {

                    foreach ($messageId['attachments'] as $key => $fileId) {

                        $messages[$messagesKey]['attachments'][$key]['signedUrl'] = "";

                        $disposition = HeaderUtils::makeDisposition('attachment', $fileId['originalFileName']);

                        $messages[$messagesKey]['attachments'][$key]['signedUrl'] = $this->storage->getBucket()->object('Attachments/'.$conversationId.'/'.$messageId['messageId'].'/'.$key)
                            ->signedUrl(time() + 3600, [
                                'responseDisposition' => $disposition
                            ]);

                    }

                }

            }

        }

        return $messages;

    }

    public function updateOrCreateNewConversation(string $loggedInUser, string $contact)
    {

        $conversationId = $this->database->getReference('userChats/'.$loggedInUser.'/'.$contact.'/')->getValue()['conversationId'];

        if(!$conversationId)
        {

        $conversationId = Uuid::uuid4();

        $UserChat = $this->database->getReference('userChats/'.$loggedInUser.'/'.$contact.'/');
        $UserChat->set(["conversationId" => $conversationId]);


        }

        return $conversationId;

    }

    public function saveFilesToStorage(array $attachments, string $conversationId, string $messageId): array
    {

            if(!$attachments)
            {
                throw new \LogicException('There Are No Attachments');
            }

            $storageBucket = $this->storage->getBucket();

            $fileData = [];

            foreach($attachments as $file){

                $fileId = Uuid::uuid4();

                $storageBucket->upload(file_get_contents($file->getPathname()), [
                    'name' => 'Attachments/'.$conversationId.'/'.$messageId.'/'.$fileId
                ]);

                $fileData = [
                    'originalFileName' => $file->getClientOriginalName(),
                    'fileType' => $file->getMimeType()

                ];

                $message_content['attachments'][$fileId->toString()] = $fileData;
            }

            return $fileData;

    }

    public function saveUserChatData(string $loggedInUser, string $contact, string $conversationId)
    {

        $this->database->getReference('userChats/'.$loggedInUser.'/'.$contact.'/')
            ->update([
                'conversationId' => $conversationId,
                'read' => 'false',
                'sender' => $loggedInUser,
                'receiver' => $contact
            ]);

        $this->database->getReference('userChats/'.$contact.'/'.$loggedInUser.'/')
            ->update([
                'conversationId' => $conversationId,
                'read' => 'false',
                'sender' => $contact,
                'receiver' => $loggedInUser
            ]);

    }


}