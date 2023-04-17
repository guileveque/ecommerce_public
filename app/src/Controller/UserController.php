<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserController extends AbstractController
{
    #[Route('api/user', name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        $users = $userRepository->findAll();
        $json = $serializer->serialize($users, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);

        return new JsonResponse($json, 200, [], true);
    }

    #[Route('api/register', name: 'app_user_new', methods: ['POST', 'PUT'])]
    public function new(Request $request, UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher, TokenStorageInterface $tokenStorageInterface, JWTTokenManagerInterface $jwtManager): Response
    {

        $content = $request->toArray();

        // Extract user data from the request
        $login = $content['login'];
        $password = $content['password'];
        $email = $content['email'];
        $firstName = $content['firstname'];
        $lastName = $content['lastname'];

        // Check if user already exists
        $existingUser = $userRepository->findOneBy(['login' => $login]);
        if ($existingUser) {
            return new JsonResponse('User already exists', Response::HTTP_FORBIDDEN);
        }

        // Create new user
        $user = new User($tokenStorageInterface, $jwtManager);
        $user->setLogin($login);
        $user->setEmail($email);
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setCreatedAt(new \DateTimeImmutable());

        // Generate password hash and set it on the user
        $passwordHash = $passwordHasher->hashPassword($user, $password);
        $user->setPassword($passwordHash);

        // Generate JWT token for the user
        // Save the user
        $userRepository->save($user, true);

        return $this->json($user);
    }

    #[Route('/api/user/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show($id, UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {

        $user = $userRepository->find($id);
        if(!$user){
            return new JsonResponse("The user doesn't exist", 404);
        }

        $json = $serializer->serialize($user, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);

        return new JsonResponse($json, 200, [], true);
    }

    #[Route('api/user/{id}', name: 'app_user_edit', methods: ['PUT'])]
    public function edit($id, Request $request, UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher, SerializerInterface $serializer): Response
    {
        $content = $request->toArray();
        $user = $userRepository->find($id);

        if(array_key_exists("login", $content) && gettype($content['login']) == "string"){
            $user->setLogin($content['login']);
        }
        if(array_key_exists("email", $content)  && gettype($content['email']) == "string"){
            $user->setEmail($content['email']);
        }
        if(array_key_exists("firstname", $content)  && gettype($content['firstname']) == "string"){
            $user->setFirstName($content['firstname']);
        }
        if(array_key_exists("lastname", $content)  && gettype($content['lastname']) == "string"){
            $user->setLastName($content['lastname']);
        }

        // Generate password hash and set it on the user
        if(array_key_exists("password", $content)  && array_key_exists("passwordConfirm", $content)  &&
            $content['password'] == $content['passwordConfirm']){
            $passwordHash = $passwordHasher->hashPassword($user, $content['password']);
            $user->setPassword($passwordHash);
        }

        // Generate JWT token for the user
        // Save the user
        $userRepository->save($user, true);

        $json = $serializer->serialize($user, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);

        return new JsonResponse($json, 200, [], true);
    }

    #[Route('/api/user/{id}', name: 'app_user_delete', methods: ['DELETE'])]
    public function delete($id, SerializerInterface $serializer, UserRepository $userRepository): Response
    {
        $user = $userRepository->find($id);

        if (!$user) { return new JsonResponse(['message'=> 'User not found'], Response::HTTP_NOT_FOUND); }

        $userRepository->remove($user, true);

        $json = $serializer->serialize($user, 'json');

        return new JsonResponse($json, 200, [], true);

    }
}
