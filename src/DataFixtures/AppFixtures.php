<?php

namespace App\DataFixtures;

use App\Entity\Contact;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $contacts = [
            [
                'nickname' => 'joe',
                'age' => 23,
                'country' => 'US',
            ],
            [
                'nickname' => 'alfred',
                'age' => 48,
                'country' => 'UK',
            ],
            [
                'nickname' => 'michel',
                'age' => 35,
                'country' => 'FR',
            ],
        ];

        foreach($contacts as $contact) {
            $user = new Contact();
            $user->setNickname($contact['nickname']);
            $user->setAge($contact['age']);
            $user->setCountry($contact['country']);

            $manager->persist($user);
        }
        
        $manager->flush();
    }
}
