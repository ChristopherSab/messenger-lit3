<?php

declare(strict_types=1);

namespace App\Tests\Behat;

use App\Entity\User;
use Behat\Behat\Context\Context;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * This context class contains the definitions of the steps used by the demo
 * feature file. Learn how to get started with Behat and BDD on Behat's website.
 *
 * @see http://behat.org/en/latest/quick_start.html
 */
final class FeatureContext implements Context
{
    private static $container;

    /** @var KernelInterface */
    private $kernel;


    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
        self::$container = $kernel->getContainer();
    }


    /**
     * @Given there is a user :username with email :email and Password :password
     */
    public function thereIsAUserWithEmailAndPassword($username, $email, $password)
    {
        $user = new User();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setPassword($password);


        $em = self::$container->get('doctrine')
            ->getManager();
        $em->persist($user);
        $em->flush();
    }


    /**
     * @BeforeScenario
     */
    public function clearData()
    {
        $em = self::$container->get('doctrine')->getManager();
        $purger = new ORMPurger($em);
        $purger->purge();
    }





}
