<?php

namespace App\Controller;

use App\Repository\PersonaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Persona;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Entity\User;

class PersonaController extends AbstractController
{
    #[Route('/persona', name: 'app_persona')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/PersonaController.php',
        ]);
    }

    // DEBUT CRUD PERSONA

    #[Route('/api/persona', name: 'persona.getAll', methods: ['GET'])]
    public function getAllPersona(PersonaRepository $personaRepository, TagAwareCacheInterface $cache, SerializerInterface $serializer) {
        $idCacheGetAllPersona = 'getAllPersonaCache';
        $jsonPersona = $cache->get($idCacheGetAllPersona, function(ItemInterface $item) use ($personaRepository, $serializer) {
            echo 'Cache miss';
            $item->tag('personaCache');
            $persona = $personaRepository->findBy(['status' => 'on']);
            return $serializer->serialize($persona, 'json');
        });

        return new JsonResponse($jsonPersona, JsonResponse::HTTP_OK, [], true);

    }
    
    #[Route('/api/persona/{id}', name: 'persona.getOne', methods: ['GET'])]
    #[IsGranted("ROLE_COACH", statusCode: 403, message: "Access denied")]
    public function getOnePersona(PersonaRepository $personaRepository, int $id, SerializerInterface $serializer) {
        $persona = $personaRepository->find($id);
        $jsonPersona = $serializer->serialize($persona, 'json');
        return new JsonResponse($jsonPersona, 200, [], true);
    }

    #[Route('/api/persona', name: 'persona.create', methods: ['POST'])]
    #[IsGranted("ROLE_COACH", statusCode: 403, message: "Access denied")]
    public function createPersona(Request $request, User $user, ValidatorInterface $validator, TagAwareCacheInterface $cache, UrlGeneratorInterface $urlGenerator, SerializerInterface $serializer, EntityManagerInterface $manager) {
        
        $data = $request->toArray();

        $persona = $serializer->deserialize($request->getContent(), Persona::class, 'json');
        $persona
            ->setNom($data['nom'] ?? '')
            ->setPrenom($data['prenom'] ?? '')
            ->setDateNaissance(new \DateTime($data['dateNaissance']))
            ->setGenre($data['genre'] ?? '')
            ->setMail($data['mail'] ?? '')
            ->setAdresse($data['adresse'] ?? '')
            ->setUsername($user->getUsername())
            ->setStatus('on')
            ->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime());


        $errors = $validator->validate($persona);
        if (count($errors) > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $manager->persist($persona);
        $manager->flush();

        $cache->invalidateTags(['personaCache']);

        $jsonPersona = $serializer->serialize($persona, 'json', ['groups' => "getAllPersona"]);
        $location = $urlGenerator->generate('persona.getOne', ['idPersona' => $persona->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        return new JsonResponse($jsonPersona, JsonResponse::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route('/api/persona/{id}', name: 'persona.update', methods: ['PUT'])]
    #[IsGranted("ROLE_COACH", statusCode: 403, message: "Access denied")]
    public function updatePersona(Request $request, PersonaRepository $personaRepository, ValidatorInterface $validator, TagAwareCacheInterface $cache, int $id, SerializerInterface $serializer, EntityManagerInterface $manager) {
        $persona = $personaRepository->find($id);
        if (!$persona) {
            return new JsonResponse("Persona not found", JsonResponse::HTTP_NOT_FOUND);
        }

        $data = $request->toArray();

        $persona
            ->setNom($data['nom'] ?? $persona->getNom())
            ->setPrenom($data['prenom'] ?? $persona->getPrenom())
            ->setDateNaissance(new \DateTime($data['dateNaissance']) ?? $persona->getDateNaissance())
            ->setGenre($data['genre'] ?? $persona->getGenre())
            ->setMail($data['mail'] ?? $persona->getMail())
            ->setAdresse($data['adresse'] ?? $persona->getAdresse())
            ->setUpdatedAt(new \DateTime());

        $errors = $validator->validate($persona);
        if (count($errors) > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $manager->persist($persona);
        $manager->flush();

        $cache->invalidateTags(['personaCache']);

        $jsonPersona = $serializer->serialize($persona, 'json', ['groups' => "getAllPersona"]);
        return new JsonResponse($jsonPersona, JsonResponse::HTTP_OK, [], true);
    }

    #[Route('/api/persona/{id}', name: 'persona.delete', methods: ['DELETE'])]
    #[IsGranted("ROLE_COACH", statusCode: 403, message: "Access denied")]
    public function deletePersona(PersonaRepository $personaRepository, TagAwareCacheInterface $cache, int $id, EntityManagerInterface $manager) {
        $persona = $personaRepository->find($id);
        if (!$persona) {
            return new JsonResponse("Persona not found", JsonResponse::HTTP_NOT_FOUND);
        }

        $persona->setStatus('off');
        $manager->flush();

        $cache->invalidateTags(['personaCache']);

        return new JsonResponse("Persona deleted", JsonResponse::HTTP_OK);
    }

    // FIN CRUD PERSONA



}
