<?php

namespace App\Controller\Security;

use App\Entity\User;
use App\Form\Type\RegisterFormType;
use App\Repository\UserRepository;
use App\Service\ObjectNormalizer;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\annotation\Route;

class SecurityController extends AbstractController
{
    private JWTEncoderInterface $jwtEncoder;
    private UserRepository $userRepository;
    private ObjectNormalizer $normalizeObject;

    public function __construct(JWTEncoderInterface $JWTEncoder, UserRepository $userRepository, ObjectNormalizer $normalizeObject)
    {
        $this->jwtEncoder = $JWTEncoder;
        $this->userRepository = $userRepository;
        $this->normalizeObject = $normalizeObject;
    }

    #[Route('/auth/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): JsonResponse
    {
        $parameter = json_decode($request->getContent(), true);
        $form = $this->createForm(RegisterFormType::class);
        $form->submit($parameter);

        if (!$form->isValid()) {
            return $this->normalizeObject->getNormalizedObject($form, 400);
        }

        $checkUser = $this->userRepository->findOneBy(['email' => $parameter['email']]);

        if ($checkUser) {
            throw new HttpException('404', 'User already exists.');
        }

        $user = new User();

        $data = $form->getData();

        $existingUser = $this->userRepository->findOneBy(['email' => $data->getEmail()]);

        if($existingUser){
            throw new HttpException(409, "A user with this email already exists.");
        }

        $hashedPassword = $passwordHasher->hashPassword($user, $data->getPassword());
        $user->setFirstname($data->getFirstname());
        $user->setLastname($data->getlastname());
        $user->setEmail($data->getEmail());
        $user->setPassword($hashedPassword);
        $user->setRoles(['ROLE_USER']);
        $user->setCreatedAt(new DateTimeImmutable());

        $entityManager->persist($user);
        $entityManager->flush();


        return new JsonResponse([
            'message' => 'Un email a été envoyé pour confirmer votre Compte',
        ]);


    }
}
