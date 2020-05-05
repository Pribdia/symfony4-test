<?php

namespace App\Controller;

use App\Entity\Activity;
use App\Entity\Contact;
use App\Entity\User;
use App\Form\ActivityType;
use App\Form\UserRegistrationType;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ActivityController extends AbstractController
{
    public function __construct(FlashyNotifier $flashy)
    {
        $this->flashy = $flashy;
    }

    /**
     * @Route("/activity", name="activity")
     */
    public function index()
    {
        $repository = $this->getDoctrine()->getRepository(Activity::class);
        $activities = $repository->findAllByOrder();

        $user = $this->getUser();

        foreach ($activities as $activity)
        {
            if ($user->ifHaveActivity($activity))
            {
                $activity->isCheck = true;
            }
            else
            {
                $activity->isCheck = false;
            }
        }

        return $this->render('activity/index.html.twig', [
            'activities' => $activities,
        ]);
    }

    /**
     * @Route("/activity/{id}/registration", name="activity_registration")
     */
    public function registration($id)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $userRepository = $entityManager->getRepository(User::class);
        $activityRepository = $entityManager->getRepository(Activity::class);

        $activty = $activityRepository->find($id);
        $user = $this->getUser();

        $today = new \DateTime();
        $age = $today->diff($user->getBirthDay(), true)->y;

        if ($user->ifHaveActivity($activty))
        {
            $this->flashy->warning('Vous êtes déja incris à cette activité !');
        }
        elseif ($activty->getNbrestPlace() < 0)
        {
            $this->flashy->warning('L\'activity est déja plein');
        }
        elseif ($age < $activty->getMinAge())
        {
            $this->flashy->warning('Vous n\'avez pas l\'âge requit ( '.$activty->getMinAge().' an(s) )');
        }
        else
        {
            $user->addActivity($activty);
            $entityManager->flush();
            $this->flashy->success('Vous vous êtes incrit à "'.$activty->getName().'".');
        }
        return $this->redirectToRoute('activity');
    }

    /**
     * @Route("/activity/{id}/unregistration", name="activity_unregistration")
     */
    public function unregistration($id)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $activityRepository = $entityManager->getRepository(Activity::class);

        $activty = $activityRepository->find($id);
        $user = $this->getUser();

        if (!$user->ifHaveActivity($activty))
        {
            $this->flashy->warning('Vous n\'êtes incris à cette activité !');
        }
        else
        {
            $user->removeActivity($activty);
            $entityManager->flush();
            $this->flashy->success('Vous vous êtes des-incrit à "'.$activty->getName().'".');
        }
        return $this->redirectToRoute('activity');
    }

    /**
     * @Route("admin/activity/new", name="activity_new")
     */
    public function new(Request $request, ValidatorInterface $validator, UserPasswordEncoderInterface $encoder)
    {
        $activity = new Activity();
        $form = $this->createForm(ActivityType::class, $activity);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $activity = $form->getData();

            $errors = $validator->validate($activity);

            if (count($errors) > 0) {
                return new Response((string) $errors, 400);
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($activity);
            $entityManager->flush();

            $this->flashy->success('Vous avez ajouter une activité !');

            return $this->redirectToRoute('activity');
        }

        return $this->render('activity/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
