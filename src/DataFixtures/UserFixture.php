<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setEmail('DonaldTrump20@thewhitehouse.org');
        $user->setUsername('Jonathan20');
        $user->setPassword('password');
        //$user->setRoles(['Role']);

        $manager->persist($user);
        $manager->flush();
    }

}
