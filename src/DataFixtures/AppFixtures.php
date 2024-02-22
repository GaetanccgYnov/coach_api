<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\Cours;
use DateTime;




class AppFixtures extends Fixture
{
    /**
     * @var Generator
     */
    private $faker;

    /**
     * Password Hasher
     * 
     * @var UserPasswordHasherInterface
     */
    private UserPasswordHasherInterface $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->faker = \Faker\Factory::create('fr_FR');
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager): void
    {   
        
        // create public user
        $publicUser = new User();
        $password = 'password';
        $publicUser
            ->setUsername("public@". $password)
            ->setUuid($this->faker->uuid)
            ->setPassword($this->userPasswordHasher->hashPassword($publicUser, $password))
            ->setRoles(["ROLE_PUBLIC"]);

        $manager->persist($publicUser);
        $manager->flush();
        
        // create 3 user
        for ($i = 0; $i < 3; $i++) {
            $user = new User();
            $password = $this->faker->password(2, 6);
            $user
                ->setUsername('user@'.$password)
                ->setUuid($this->faker->uuid)
                ->setPassword($this->userPasswordHasher->hashPassword($user, $password))
                ->setRoles(["ROLE_USER"]);
            $manager->persist($user);
        }
        $manager->flush();

        // create admin user
        $adminUser = new User();
        $password = 'password';
        $adminUser
            ->setUsername("admin")
            ->setUuid($this->faker->uuid)
            ->setPassword($this->userPasswordHasher->hashPassword($adminUser, $password))
            ->setRoles(["ROLE_ADMIN"]);
            $manager->persist($adminUser);
        $manager->flush();

        // Enfants 5 à 8 ans - Samedi
        $this->createCours($manager, 'Classe MMA Mixte Enfants 5 à 8 ans', 'Samedi', '09:00', '10:00', 14);
        
        // SMS Enfants 5 à 8 ans - Samedi
        $this->createCours($manager, 'SMS Enfants 5 à 8 ans', 'Samedi', '14:00', '15:00', 14);
        
        // Enfants 8 à 12 ans - Samedi
        $this->createCours($manager, 'Classe MMA Mixte Enfants 8 à 12 ans', 'Samedi', '15:00', '16:00', 16);
        
        // Classe ADO - Samedi
        $this->createCours($manager, 'Classe ADO', 'Samedi', '16:00', '17:30', 20);
        
        // Circuit Training - Samedi
        $this->createCours($manager, 'Circuit Training', 'Samedi', '17:30', '18:30', 14);
        
        // SMS Enfants 8 à 12 ans - Samedi
        $this->createCours($manager, 'SMS Enfants 8 à 12 ans', 'Samedi', '10:00', '11:00', 16);
        
        // Classe MMA Mixte ADO - Samedi
        $this->createCours($manager, 'Classe MMA Mixte ADO', 'Samedi', '11:00', '12:30', 20);
        
        // Grappling Mixte - Samedi
        $this->createCours($manager, 'Grappling Mixte', 'Samedi', '18:30', '20:00', 30);
        
        // Pied-poing Classe 1 - Vendredi
        $this->createCours($manager, 'Pied-poing Classe 1', 'Vendredi', '19:00', '20:30', 22);
        
        // MMA Vétéran - Vendredi
        $this->createCours($manager, 'MMA Vétéran', 'Vendredi', '19:15', '20:15', 18);
        
        // Pied-poing Classe 2 et 3 - Vendredi
        $this->createCours($manager, 'Pied-poing Classe 2 et 3', 'Vendredi', '20:30', '22:00', 20);
        
        // Body MMA - Vendredi
        $this->createCours($manager, 'Body MMA', 'Vendredi', '19:30', '20:30', 14);
        
        // Sparring Assaut - Vendredi
        $this->createCours($manager, 'Sparring Assaut', 'Vendredi', '18:30', '19:30', 30);
        
        // Circuit Training (2) - Vendredi
        $this->createCours($manager, 'Circuit Training', 'Vendredi', '20:15', '21:15', 16);

        $manager->flush();

    }

    private function createCours(ObjectManager $manager, string $nom, string $jour, string $heureDebut, string $heureFin, int $placesDisponibles): void
    {
        $cours = new Cours();
        $cours->setNom($nom)
              ->setJour($jour)
              ->setHeureDebut(new DateTime($heureDebut))
              ->setHeureFin(new DateTime($heureFin))
              ->setPlacesDisponibles($placesDisponibles)
              ->setStatus('on')
              ->setCreatedAt(new DateTime())
              ->setUpdatedAt(new DateTime());

        $manager->persist($cours);
    }
}
