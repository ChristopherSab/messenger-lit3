<?php


namespace App\Controller;

use App\Entity\Profile;
use App\Form\ProfileFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ProfileRegistrationController extends AbstractController
{

    /**
     * @Route("/register_profile", name="app_register_profile")
     */
    public function registerProfile(Request $request)
    {
        $profile = new Profile();
        $form = $this->createForm(ProfileFormType::class, $profile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $profile->setBio($form->get('bio')->getData());
            $profile->setBio($form->get('city')->getData());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($profile);
            $entityManager->flush();
            // do anything else you need here, like send an email

            return $this->redirectToRoute('chat_home');
        }

        return $this->render('profile/index.html.twig', [
            'profileForm' => $form->createView(),
        ]);
    }

}