<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\Cours;
use DateTime;
use App\Entity\Persona;




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
        
        // Création d'un utilisateur "public"
        $this->createUser($manager, "public@example.com", "password", ["ROLE_PUBLIC"]);

        // Création de 3 utilisateurs "élèves"
        for ($i = 0; $i < 3; $i++) {
            $this->createUser($manager, $this->faker->email, $this->faker->password(8, 20), ["ROLE_ELEVE"]);
        }

        // Création d'un utilisateur "coach" avec des identifiants spécifiques
        $this->createUser($manager, "admin@example.com", "password", ["ROLE_COACH"]);


        // create cours
        // Lundi
        $this->addCour($manager, 'Pied-poing Classe 1', 'Lundi', '19:00', '20:30');
        $this->addCour($manager, 'MMA Vétéran', 'Lundi', '19:15', '20:15');
        $this->addCour($manager, 'Pied-poing Classe 2 et 3', 'Lundi', '20:30', '22:00');

        // Mardi
        $this->addCour($manager, 'GRAPPLING MIXTE', 'Mardi', '18:30', '20:00');
        $this->addCour($manager, 'Body MMA (1)', 'Mardi', '19:30', '20:30');

        // Mercredi
        $this->addCour($manager, 'Enfants 5 à 8 ans', 'Mercredi', '14:00', '15:00');
        $this->addCour($manager, 'Enfants 8 à 12 ans', 'Mercredi', '15:00', '16:00');
        $this->addCour($manager, 'Classe ADO', 'Mercredi', '16:00', '17:30');
        $this->addCour($manager, 'Circuit Training (2)', 'Mercredi', '17:30', '18:30');
        $this->addCour($manager, 'MMA Vétéran(1)', 'Mercredi', '19:15', '20:15');

        // Jeudi
        $this->addCour($manager, 'Pied-poing Classe 1', 'Jeudi', '19:00', '20:30');
        $this->addCour($manager, 'Pied-poing Classe 2 et 3', 'Jeudi', '20:30', '22:00');

        // Vendredi
        $this->addCour($manager, 'Sparring Assaut *', 'Vendredi', '18:30', '19:30');
        $this->addCour($manager, 'Body MMA (1)', 'Vendredi', '19:30', '20:30');
        $this->addCour($manager, 'Circuit Training (2)', 'Vendredi', '20:15', '21:15');

        // Samedi
        $this->addCour($manager, 'Enfants 5 à 8 ans', 'Samedi', '09:00', '10:00');
        $this->addCour($manager, 'Enfants 8 à 12 ans', 'Samedi', '10:00', '11:00');
        $this->addCour($manager, 'Classe MMA Mixte ADO', 'Samedi', '11:00', '12:30');
        $this->addCour($manager, 'Classe MMA Mixte ADULTE', 'Samedi', '09:30', '11:00');

        $manager->flush();
    }

    private function createUser(ObjectManager $manager, string $email, string $password, array $roles): void
    {
        $user = new User();
        $persona = new Persona();

        $user->setEmail($email)
             ->setPassword($this->userPasswordHasher->hashPassword($user, $password))
             ->setRoles($roles);

        $persona->setNom($this->faker->lastName)
                ->setPrenom($this->faker->firstName)
                ->setDateNaissance($this->faker->dateTimeBetween('-100 years', '-18 years'))
                ->setGenre($this->faker->randomElement(['homme', 'femme']))
                ->setMail($user->getEmail()) // Utiliser le même email que l'utilisateur
                ->setAdresse($this->faker->address)
                ->setStatus('on')
                ->setCreatedAt(new DateTime())
                ->setUpdatedAt(new DateTime());

        $user->setPersona($persona);

        $manager->persist($user);
        $manager->persist($persona);
    }

    private function addCour(ObjectManager $manager, string $nom, string $jour, string $heureDebut, string $heureFin)
    {
        $cours = new Cours();
        $cours->setNom($nom)
              ->setJour($jour)
              ->setHeureDebut(new DateTime($heureDebut))
              ->setHeureFin(new DateTime($heureFin))
              ->setPlacesDisponibles($this->faker->numberBetween(14, 40))
              ->setStatus('on')
              ->setCreatedAt(new DateTime())
              ->setUpdatedAt(new DateTime());

        $manager->persist($cours);
    }
}
