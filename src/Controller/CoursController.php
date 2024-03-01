<?php

namespace App\Controller;

use App\Repository\CoursRepository;
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
use App\Entity\Cours;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Security\Core\Security;
use App\Repository\UserRepository;
use App\Entity\CoursAccess;

class CoursController extends AbstractController
{
    #[Route('/cours', name: 'app_cours')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/CoursController.php',
        ]);
    }

    // DEBUT CRUD COURS

    #[Route('/api/cours', name: 'cours.getAll', methods: ['GET'])]
    public function getAllCours(CoursRepository $coursRepository, TagAwareCacheInterface $cache, SerializerInterface $serializer) {
        $idCacheGetAllCours = 'getAllCoursCache';
        $jsonCours = $cache->get($idCacheGetAllCours, function(ItemInterface $item) use ($coursRepository, $serializer) {
            echo 'Cache miss';
            $item->tag('coursCache');
            $cours = $coursRepository->findBy(['status' => 'on']);
            return $serializer->serialize($cours, 'json', ['groups' => 'getAllCours']);
        });

        return new JsonResponse($jsonCours, JsonResponse::HTTP_OK, [], true);

    }
    
    #[Route('/api/cours/{id}', name: 'cours.getOne', methods: ['GET'])]
    #[IsGranted("ROLE_COACH", statusCode: 403, message: "Access denied")]
    public function getOneCours(CoursRepository $coursRepository, int $id, SerializerInterface $serializer) {
        $cours = $coursRepository->find($id);
        $jsonCours = $serializer->serialize($cours, 'json', ['groups' => 'getAllCours']);
        return new JsonResponse($jsonCours, JsonResponse::HTTP_OK, [], true);
    }

    #[Route('/api/cours', name: 'cours.create', methods: ['POST'])]
    #[IsGranted("ROLE_COACH", statusCode: 403, message: "Access denied")]
    public function createCours(Request $request, ValidatorInterface $validator, TagAwareCacheInterface $cache, UrlGeneratorInterface $urlGenerator, SerializerInterface $serializer, EntityManagerInterface $manager, CoursRepository $coursRepository, UserRepository $userRepository) {
        $data = $request->toArray();

        $cours = $serializer->deserialize($request->getContent(), Cours::class, 'json');
        $cours
            ->setNom($data['nom'] ?? '')
            ->setDescription($data['description'] ?? '')
            ->setJour($data['jour'] ?? '')
            ->setHeureDebut(new \DateTime($data['heureDebut']))
            ->setHeureFin(new \DateTime($data['heureFin']))
            ->setPlacesDisponibles($data['placesDisponibles'] ?? 0)
            ->setStatus('on')
            ->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime());

        $accessesData = [];
        if (!empty($data['accessUserIds'])) {
            foreach ($data['accessUserIds'] as $userId) {
                $user = $userRepository->find($userId);
                if ($user) {
                    $coursAccess = new CoursAccess();
                    $coursAccess->setUser($user)
                                ->setCours($cours)
                                ->setCreatedAt(new \DateTime())
                                ->setUpdatedAt(new \DateTime())
                                ->setStatus('granted');
                    $manager->persist($coursAccess);
                    $accessesData[] = $coursAccess;
                }
            }
        } else {
            // If no specific users are specified, grant access to all users with roles ROLE_COACH or ROLE_ELEVE
            $users = $userRepository->findByRoles(['ROLE_COACH', 'ROLE_ELEVE']);
            foreach ($users as $user) {
                $coursAccess = new CoursAccess();
                $coursAccess->setUser($user)
                            ->setCours($cours)
                            ->setCreatedAt(new \DateTime())
                            ->setUpdatedAt(new \DateTime())
                            ->setStatus('granted');
                $manager->persist($coursAccess);
                $accessesData[] = $coursAccess;
            }
        }

        $errors = $validator->validate($cours);
        if (count($errors) > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $manager->persist($cours);
        $manager->flush();
        $cache->invalidateTags(['coursCache']);

        // Serialize and combine data for response
        $coursData = $serializer->serialize($cours, 'json', ['groups' => 'getAllCours']);
        $accessesSerialized = array_map(function ($access) use ($serializer) {
            return json_decode($serializer->serialize($access, 'json', ['groups' => ['getWhoAccess', 'groupForCours', 'groupForUser']]), true);
        }, $accessesData);

        $combinedData = json_decode($coursData, true);
        $combinedData['accesses'] = $accessesSerialized;

        $location = $urlGenerator->generate('cours.getOne', ['id' => $cours->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        return new JsonResponse($combinedData, JsonResponse::HTTP_CREATED, ["Location" => $location]);
    }


    #[Route('/api/cours/{id}', name: 'cours.update', methods: ['PUT'])]
    #[IsGranted("ROLE_COACH", statusCode: 403, message: "Access denied")]
    public function updateCours(Request $request, CoursRepository $coursRepository, ValidatorInterface $validator, TagAwareCacheInterface $cache, int $id, SerializerInterface $serializer, EntityManagerInterface $manager) {
        $cours = $coursRepository->find($id);
        if (!$cours) {
            return new JsonResponse("Cours not found", JsonResponse::HTTP_NOT_FOUND);
        }

        $data = $request->toArray();

        $cours
            ->setNom($data['nom'] ?? $cours->getNom())
            ->setDescription($data['description'] ?? $cours->getDescription())
            ->setJour($data['jour'] ?? $cours->getJour())
            ->setHeureDebut(new \DateTime($data['heureDebut'] ?? $cours->getHeureDebut()))
            ->setHeureFin(new \DateTime($data['heureFin'] ?? $cours->getHeureFin()))
            ->setPlacesDisponibles($data['placesDisponibles'] ?? $cours->getPlacesDisponibles())
            ->setUpdatedAt(new \DateTime());

        $errors = $validator->validate($cours);
        if (count($errors) > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $manager->persist($cours);
        $manager->flush();

        $cache->invalidateTags(['coursCache']);

        $jsonCours = $serializer->serialize($cours, 'json', ['groups' => "getAllCours"]);
        return new JsonResponse($jsonCours, JsonResponse::HTTP_OK, [], true);
    }

    #[Route('/api/cours/{id}', name: 'cours.delete', methods: ['DELETE'])]
    #[IsGranted("ROLE_COACH", statusCode: 403, message: "Access denied")]
    public function deleteCours(CoursRepository $coursRepository, TagAwareCacheInterface $cache, int $id, EntityManagerInterface $manager) {
        $cours = $coursRepository->find($id);
        if (!$cours) {
            return new JsonResponse("Cours not found", JsonResponse::HTTP_NOT_FOUND);
        }

        $cours->setStatus('off');
        $manager->flush();

        $cache->invalidateTags(['coursCache']);

        return new JsonResponse("Cours suprimé", JsonResponse::HTTP_OK);
    }

    // FIN CRUD COURS

    // DEBUT INSCRIPTION COURS

    #[Route('/api/cours/{id}/inscrire', name: 'cours.inscrire', methods: ['POST'])]
    public function inscrire(int $id, CoursRepository $coursRepository, Security $security, EntityManagerInterface $manager)
    {
        $cours = $coursRepository->find($id);
        if (!$cours) {
            return new JsonResponse("Cours not found", JsonResponse::HTTP_NOT_FOUND);
        }

        $user = $security->getUser();
        if (!$user) {
            return new JsonResponse("User not authenticated", JsonResponse::HTTP_UNAUTHORIZED);
        }

        $cours->addUser($user);
        $manager->flush();

        return new JsonResponse("User successfully enrolled in the course", JsonResponse::HTTP_OK);
    }

    #[Route('/api/cours/{id}/desinscrire', name: 'cours.desinscrire', methods: ['POST'])]
    public function desinscrire(int $id, CoursRepository $coursRepository, Security $security, EntityManagerInterface $manager)
    {
        $cours = $coursRepository->find($id);
        if (!$cours) {
            return new JsonResponse("Cours not found", JsonResponse::HTTP_NOT_FOUND);
        }

        $user = $security->getUser();
        if (!$user) {
            return new JsonResponse("User not authenticated", JsonResponse::HTTP_UNAUTHORIZED);
        }

        $cours->removeUser($user);
        $manager->flush();

        return new JsonResponse("User successfully unenrolled in the course", JsonResponse::HTTP_OK);
    }

    // FIN INSCRIPTION COURS
}
