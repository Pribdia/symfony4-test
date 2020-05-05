<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserLoginType;
use App\Form\UserRegistrationType;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SecurityController extends AbstractController
{
    public function __construct(FlashyNotifier $flashy)
    {
        $this->flashy = $flashy;
    }

    /**
     * @Route("/login", name="login")
     */

    public function login(Request $request, ValidatorInterface $validator, AuthenticationUtils $authenticationUtils)
    {
        $lastUsername = $authenticationUtils->getLastUsername();
        $error = $authenticationUtils->getLastAuthenticationError();

        $user = new User();
        $form = $this->createForm(UserLoginType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted())
        {
            $user = $form->getData();

            $this->flashy->success('Vous êtes connecté !');

            return $this->redirectToRoute('contact_index');
        }

        return $this->render('security/login.html.twig', [
            'form' => $form->createView(),
            'lastUsername' => $lastUsername,
            'error' => $error
        ]);
    }

    /**
     * @Route("/registration", name="registration")
     */
    public function registration(Request $request, ValidatorInterface $validator, UserPasswordEncoderInterface $encoder)
    {
        $user = new User();
        $form = $this->createForm(UserRegistrationType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $user = $form->getData();

            $errors = $validator->validate($user);

            if (count($errors) > 0) {
                return new Response((string) $errors, 400);
            }
            $user->setPassword($encoder->encodePassword($user,$user->getPassword()));
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $this->flashy->success('Vous pouvez vous connecter !');

            return $this->redirectToRoute('login');
        }

        return $this->render('security/registration.html.twig', [
            'form' => $form->createView(),
        ]);

        return $this->render('security/registration.html.twig', [
            'controller_name' => 'SecurityController',
        ]);
    }

}
