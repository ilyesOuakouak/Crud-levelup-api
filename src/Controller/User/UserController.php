<?php

namespace App\Controller\User;

use App\Repository\UserRepository;
use App\Service\ObjectNormalizer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class UserController extends AbstractController
{
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;
    private ObjectNormalizer $normalizer;
    public function __construct(UserRepository $userRepository, EntityManagerInterface $entityManager, ObjectNormalizer $normalizeObject)
    {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->normalizer = $normalizeObject;
    }

    #[Route('/admin/users', 'user_list', methods: ['GET'])]
    public function getUsers(TagAwareCacheInterface $cache): JsonResponse
    {
        $repo = $this->userRepository;

        $users = $repo->findAll();
        dump($users);

        $users = $cache->get('user_list', function (ItemInterface $item) use (&$repo) {
            $item->expiresAfter(20); // cache for 20 seconds

            $item->tag('users_list');
            return $repo->findAll();
        });



        return $this->normalizer->getNormalizedObject($users, 200, ['user_data']);
    }
}
