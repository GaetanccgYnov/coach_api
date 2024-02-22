<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use App\Repository\coachRepository;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\coach;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class CoachController extends AbstractController
{
    #[Route('/coach', name: 'app_coach')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/CoachController.php',
        ]);
    }

    // DEBUT CRUD COACH

    #[Route('/api/coach', name: 'coach.getAll', methods: ['GET'])]
    #[IsGranted("ROLE_ADMIN", statusCode: 403, message: "Access denied")]
    public function getAllCoach(coachRepository $coachRepository, TagAwareCacheInterface $cache, SerializerInterface $serializer) {
        $idCacheGetAllCoach = 'getAllCoachCache';
        $jsonCoach = $cache->get($idCacheGetAllCoach, function(ItemInterface $item) use ($coachRepository, $serializer) {
            echo 'Cache miss';
            $item->tag('coachCache');
            $coach = $coachRepository->findBy(['status' => 'on']);
            return $serializer->serialize($coach, 'json');
        });
        
        return new JsonResponse($jsonCoach, JsonResponse::HTTP_OK, [], true);
    }

    #[Route('/api/coach/{id}', name: 'coach.getOne', methods: ['GET'])]
    #[IsGranted("ROLE_ADMIN", statusCode: 403, message: "Access denied")]
    public function getOnecoach(coachRepository $coachRepository, int $id, SerializerInterface $serializer) {
        $coach = $coachRepository->find($id);
        $jsoncoach = $serializer->serialize($coach, 'json');
        return new JsonResponse($jsoncoach, 200, [], true);
    }

    #[Route('/api/coach', name: 'coach.create', methods: ['POST'])]
    #[IsGranted("ROLE_ADMIN", statusCode: 403, message: "Access denied")]
    public function createcoach(Request $request, ValidatorInterface $validator, TagAwareCacheInterface $cache, UrlGeneratorInterface $urlGenerator, SerializerInterface $serializer, EntityManagerInterface $manager) {
        
        $data = $request->toArray();

        $coach = $serializer->deserialize($request->getContent(), coach::class, 'json');
        $coach
            ->setNom($data['nom'] ?? '')
            ->setPrenom($data['prenom'] ?? '')
            ->setDiscipline($data['discipline'] ?? '')
            ->setStatus('on')
            ->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime());

        $errors = $validator->validate($coach);
        if (count($errors) > 0) {
            return new JsonResponse($errors, JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $manager->persist($coach);
        $manager->flush();

        $cache->invalidateTags(['coachCache']);

        $jsoncoach = $serializer->serialize($coach, 'json', ['groups' => "getAllcoach"]);
        $location = $urlGenerator->generate('coach.getOne', ['idcoach' => $coach->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        return new JsonResponse($jsoncoach, JsonResponse::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route('/api/coach/{id}', name: 'coach.update', methods: ['PUT'])]
    #[IsGranted("ROLE_ADMIN", statusCode: 403, message: "Access denied")]
    public function updatecoach(Request $request, ValidatorInterface $validator, TagAwareCacheInterface $cache, SerializerInterface $serializer, EntityManagerInterface $manager, int $id) {
        $coach = $manager->getRepository(coach::class)->find($id);
        if (!$coach) {
            return new JsonResponse("coach not found", JsonResponse::HTTP_NOT_FOUND, [], true);
        }

        $data = $request->toArray();
        $coach
            ->setNom($data['nom'] ?? $coach->getNom())
            ->setPrenom($data['prenom'] ?? $coach->getPrenom())
            ->setDiscipline($data['discipline'] ?? $coach->getDiscipline())
            ->setUpdatedAt(new \DateTime());

        $errors = $validator->validate($coach);
        if (count($errors) > 0) {
            return new JsonResponse($errors, JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $manager->flush();
        $cache->invalidateTags(['coachCache']);

        $jsoncoach = $serializer->serialize($coach, 'json', ['groups' => "getAllcoach"]);
        return new JsonResponse($jsoncoach, JsonResponse::HTTP_OK, [], true);
    }

    #[Route('/api/coach/{id}', name: 'coach.delete', methods: ['DELETE'])]
    #[IsGranted("ROLE_ADMIN", statusCode: 403, message: "Access denied")]
    public function deletecoach(EntityManagerInterface $manager, int $id, TagAwareCacheInterface $cache) {
        $coach = $manager->getRepository(coach::class)->find($id);
        if (!$coach) {
            return new JsonResponse("coach not found", JsonResponse::HTTP_NOT_FOUND, [], true);
        }

        $coach->setStatus('off');
        $manager->flush();
        $cache->invalidateTags(['coachCache']);

        return new JsonResponse("coach deleted", JsonResponse::HTTP_OK, [], true);
    }

    // FIN CRUD COACH

}
