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
            // Get the enviornnment variable API_TOKEN for api calls
            $this->apiToken = $_SERVER['API_TOKEN'];
        }

    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('fr_FR');
        // Create 15 fake users thanks to Faker package
        for($i = 1; $i < 15; $i++){
            $user = new User();
            $user->setEmail($faker->unique()->safeEmail());
            $user->setPassword($this->encoder->hashPassword($user, 'password'));
            $user->setName($faker->name());
            $user->setAlignment('good');
            $user->setRoles(['ROLE_USER']);

            $manager->persist($user);
        }

        // Get Professor X data with the superheroapi
        $response = $this->client->request(
            'GET',
            'https://superheroapi.com/api/' . $this->apiToken . '/search/Professor X'
        );

        // Decode the json response to get name, id & alignment
        $professorX = json_decode($response->getContent(), true);
        $name = $professorX['results'][0]['name'];
        $id = $professorX['results'][0]['id'];
        $alignment = $professorX['results'][0]['biography']['alignment'];

        // Create New user for professor X with all the data we got from superheroapi
        $superAdmin = new User();
        $superAdmin->setEmail('professorX@gmail.com');
        $superAdmin->setPassword($this->encoder->hashPassword($superAdmin, 'admin'));
        $superAdmin->setName($name);
        $superAdmin->setAlignment($alignment);
        // Add the admin role
        $superAdmin->setRoles(['ROLE_ADMIN']);
        $manager->persist($superAdmin);

        // Get data from superheroapi then create either hero or an evil thanks to the alignment property
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

            // If there are less than 25 super heros already created && the alignment we get is 'good', create a super hero
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
            // If there are less than 50 super heros already created && the alignment we get is 'bad', create a evil
                echo 'Evil ' . $evils . ' created: ' . $name . PHP_EOL;
                $evil = new Evils();
                $evil->setId($id);
                $evil->setName($name);
                $evil->setAlignment($alignment);
                $evils++;
                $manager->persist($evil);
                continue;
            } else if($superHeros === 25 && $evils === 50) {
                // If we already created 25 super heros & 50 evils, break the loop 
                break;
            }
        }

        // For each array of value, create a status or priority
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

        // Create 5 missions
        for($i = 0; $i < 5; $i++){
            $task = new Task();
            $task->setName('Ma mission n°' . $i);
            $task->setDescription('Ma description de mission');
            $task->setDeadline(new \DateTime());
            $task->setClient($user);
            $task->setPriority($priority);
            $task->setRealisationDate(new \DateTime());
            $task->setStatus($status);
            $task->addEvil($evil);
            $task->addSuperHero($superHero);
            $evil->setTask($task);
            $manager->persist($task);
            $manager->persist($evil);
        }

        $manager->flush();
    }
}
