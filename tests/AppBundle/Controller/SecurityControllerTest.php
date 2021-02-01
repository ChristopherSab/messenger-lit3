<?php


namespace App\Tests\AppBundle\Controller;


use App\Entity\User;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;


class SecurityControllerTest extends WebTestCase
{

    private $entityManager;

    private $client;


    protected function setUp(): void
    {

        $this->client = $this->makeClient();
        $kernel = self::bootKernel();
        $this->entityManager =$kernel->getContainer()->get('doctrine')->getManager();

        parent::setUp();

        $purger = new ORMPurger($this->entityManager);
        $purger->purge();
    }

    public function testLoginFormsEmptyUserInputValidation()
    {
        $crawler = $this->client->request('GET', '/login');
        $this->assertStatusCode(200, $this->client);

        $form = $crawler->selectButton('login_form[login]')->form();

        $this->client->submit($form);

        $this->assertStatusCode(302, $this->client);
        $this->assertResponseRedirects('/login');
    }

    public function testLoginFormsEmptyPasswordValidation()
    {
        $crawler = $this->client->request('GET', '/login');
        $this->assertStatusCode(200, $this->client);

        $form = $crawler->selectButton('login_form[login]')->form();

        $formData = [
            'login_form[username]' => 'SuperUser'
        ];

        $form->setValues($formData);
        $this->client->submit($form);

        $this->assertStatusCode(302, $this->client);
        $this->assertResponseRedirects('/login');
    }




    public function testRegistrationIsSuccessful()
    {
        $crawler = $this->client->request('GET', '/register');
        $this->assertStatusCode(200, $this->client);

        $form = $crawler->selectButton('registration_form[register]')->form();

        $this->client->submit($form);

        $formData = [
            'registration_form[username]' => 'SomeUser',
            'registration_form[plainPassword]' => 'CoolPassword',
            'registration_form[email]' => 'email@example.com'
        ];

        $form->setValues($formData);
        $this->client->submit($form);

        $this->assertValidationErrors([], $this->client->getContainer());

        $this->assertStatusCode(302, $this->client);
        $this->assertResponseRedirects('/query');


        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'email@example.com']);
        $this->assertNotNull($user);
        $this->assertInstanceOf(User::class, $user);
        $this->assertSame('SomeUser', $user->getUsername());
    }


    protected function tearDown(): void
    {
        parent::tearDown();

        // close entity manager to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }


}