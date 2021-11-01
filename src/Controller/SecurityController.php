<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Profile;
use App\Entity\User;
use App\Form\LoginFormType;
use App\Form\RegistrationFormType;
use App\Security\LoginFormAuthenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    public const CHAT_HOME_ROUTE = 'chat_home';
    private const LOGIN_TWIG_FILE = 'security/login.html.twig';
    private const REGISTRATION_TWIG_FILE = 'security/register.html.twig';

    /**
     * @Route("/register", name="app_register")
     * 
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param LoginFormAuthenticator $login
     * @param GuardAuthenticatorHandler $guard
     * 
     * @return Response
     */
    public function register(
        Request $request, 
        UserPasswordEncoderInterface $passwordEncoder, 
        LoginFormAuthenticator $login, 
        GuardAuthenticatorHandler $guard
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $guard->authenticateUserAndHandleSuccess(
                $user, 
                $request, 
                $login, 
                'main'
            );

            return $this->redirectToRoute(self::CHAT_HOME_ROUTE);
        }

        return $this->render(self::REGISTRATION_TWIG_FILE, [
            'registrationForm' => $form->createView(),
        ]);
    }


    /**
     * @Route("/login", name="app_login")
     * 
     * @param AuthenticationUtils $authenticationUtils
     * 
     * @return Response
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();

        $lastUsername = $authenticationUtils->getLastUsername();

        $form = $this->createForm(LoginFormType::class);

        return $this->render(
            self::LOGIN_TWIG_FILE, [
            'last_username' => $lastUsername, 
            'error' => $error,
            'loginForm' => $form->createView()
            ]
        );
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout(): void
    {
        throw new \LogicException('');
    }
}
