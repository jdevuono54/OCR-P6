<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    /**
     * Formulaire d'inscription
     *
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();

        // On crée le formulaire
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        // Si le formulaire est send & valide on crée l'user
        if ($form->isSubmitted() && $form->isValid()) {

            // on hash le mdp
            $user->setPassword(
            $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            // On flush l'user
            $entityManager->persist($user);
            $entityManager->flush();

            // On envoi le mail de confirmation
            $this->sendConfirmationEmail($user);

            // On ajoute une notif
            $this->addFlash('success', 'Un email de confirmation vous a été envoyé');

            // On redirige sur la page d'accueil
            return $this->redirectToRoute('homepage');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * Permet de vérifier un lien de confirmation de compte
     *
     * @Route("/verify/email", name="app_verify_email")
     */
    public function verifyUserEmail(Request $request, UserRepository $userRepository): Response
    {
        $id = $request->get('id');

        // Si il n'y a pas d'id dans le lien on redirige
        if (null === $id) {
            return $this->redirectToRoute('app_register');
        }

        $user = $userRepository->find($id);

        // Si l'user n'existe pas on redirige
        if (null === $user) {
            return $this->redirectToRoute('app_register');
        }

        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('error', $exception->getReason());

            return $this->redirectToRoute('app_register');
        }

        $this->addFlash('success', 'Votre email a été vérifié');

        return $this->redirectToRoute('homepage');
    }

    /**
     * Permet de renvoyer le mail de confirmation
     *
     * @Route("/verify/email/resend/{id}", name="app_resend_verify_email")
     */
    public function resendVerifyUserEmail(Request $request,UserRepository $userRepository){
        $id = $request->get('id');

        // Si il n'y a pas d'id dans le lien on redirige
        if (null === $id) {
            return $this->redirectToRoute('app_register');
        }

        $user = $userRepository->find($id);

        // Si l'user n'existe pas on redirige
        if (null === $user) {
            return $this->redirectToRoute('app_register');
        }

        // On envoi le mail de confirmation
        $this->sendConfirmationEmail($user);

        // On crée une notif & on redirige vers la page d'accueil
        $this->addFlash('success', 'Un email de confirmation vous a été envoyé');

        return $this->redirectToRoute('homepage');
    }

    /**
     * Méthode pour envoyé le mail de confirmation d'inscription
     *
     * @param User $user
     *
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public function sendConfirmationEmail(User $user){
        $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
            (new TemplatedEmail())
                ->from(new Address('mailer@jdevuono.com', 'Jdevuono'))
                ->to($user->getEmail())
                ->subject('Confirmation de votre email')
                ->htmlTemplate('registration/confirmation_email.html.twig')
        );
    }
}
