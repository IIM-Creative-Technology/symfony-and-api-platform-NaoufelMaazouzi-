<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Task;
use App\Entity\User;
use App\Entity\Evils;
use App\Entity\Priority;
use App\Entity\Status;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Faker;

class AppFixtures extends Fixture
{
    private $encoder;
    private $client;
    private $apiToken;

        public function __construct(UserPasswordHasherInterface $encoder, HttpClientInterface $client)
        {
            $this->encoder = $encoder;
            $this->client = $client;
            $this->apiToken = $_SERVER['API_TOKEN'];
        }

    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('fr_FR');

        for($i = 1; $i < 15; $i++){
            $user = new User();
            $user->setEmail($faker->unique()->safeEmail());
            $user->setPassword($this->encoder->hashPassword($user, 'password'));
            $user->setName($faker->name());
            $user->setAlignment('good');
            $user->setRoles(['ROLE_USER']);

            $manager->persist($user);
        }

        $response = $this->client->request(
            'GET',
            'https://superheroapi.com/api/' . $this->apiToken . '/search/Professor X'
        );

        $professorX = json_decode($response->getContent(), true);
        $name = $professorX['results'][0]['name'];
        $id = $professorX['results'][0]['id'];
        $alignment = $professorX['results'][0]['biography']['alignment'];

        $superAdmin = new User();
        $superAdmin->setEmail('professorX@gmail.com');
        $superAdmin->setPassword($this->encoder->hashPassword($superAdmin, 'admin'));
        $superAdmin->setName($name);
        $superAdmin->setAlignment($alignment);
        $superAdmin->setRoles(['ROLE_ADMIN']);
        $manager->persist($superAdmin);

        $superHeros = 0;
        $evils = 0;
        $i = 1;
        for (; ; ) {
            $content = $this->client->request(
                'GET',
                'https://superheroapi.com/api/' . $this->apiToken . '/' . $i
            );
            $json = json_decode($content->getContent(), true);
            $name = $json['name'];
            $alignment = $json['biography']['alignment'];
            $i++;

            if ($alignment === 'good' && $name && $superHeros < 25) {
                echo 'User ' . $superHeros . ' created: ' . $name . PHP_EOL;
                $superHero = new User();
                $superHero->setId($id);
                $superHero->setEmail($name . '@gmail.com');
                $superHero->setPassword($this->encoder->hashPassword($superHero, $name));
                $superHero->setName($name);
                $superHero->setEmail($name . $superHeros . '@gmail.com');
                $superHero->setAlignment($alignment);
                $superHero->setRoles(['ROLE_SUPER_HERO']);
                $superHeros++;
                $manager->persist($superHero);
                continue;
            } else if($alignment === 'bad' && $evils < 50) {
                echo 'Evil ' . $evils . ' created: ' . $name . PHP_EOL;
                $evil = new Evils();
                $evil->setId($id);
                $evil->setName($name);
                $evil->setAlignment($alignment);
                $evils++;
                $manager->persist($evil);
                continue;
            } else if($superHeros === 25 && $evils === 50) {
                break;
            }
        }


        $priorityArray = ['Faible', 'Moyen', 'Elevé'];
        $statusArray = ['A valider', 'A faire', 'En cours', 'Fait'];
        foreach ($priorityArray as $value) {
            $priority = new Priority();
            $priority->setName($value);
            $manager->persist($priority);
        }
        foreach ($statusArray as $value) {
            $status = new Status();
            $status->setName($value);
            $manager->persist($status);
        }

        for($i = 0; $i < 5; $i++){
            $newEvil = new Evils();
            $newEvil->setName('test' . $i);
            $newEvil->setAlignment('bad');

            $task = new Task();
            $task->setName('Ma mission n°' . $i);
            $task->setDescription('Ma description de mission');
            $task->setDeadline(new \DateTime());
            $task->setClient($user);
            $task->setPriority($priority);
            $task->setRealisationDate(new \DateTime());
            $task->setStatus($status);
            $task->addEvil($newEvil);
            $task->addSuperHero($superHero);
            $newEvil->setTask($task);
            $manager->persist($task);
            $manager->persist($newEvil);
        }

        $manager->flush();
    }
}
