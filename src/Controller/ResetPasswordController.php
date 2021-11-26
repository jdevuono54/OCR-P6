<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

/**
 * @Route("/reset-password")
 */
class ResetPasswordController extends AbstractController
{
    use ResetPasswordControllerTrait;

    private $resetPasswordHelper;
    private $entityManager;

    public function __construct(ResetPasswordHelperInterface $resetPasswordHelper, EntityManagerInterface $entityManager)
    {
        $this->resetPasswordHelper = $resetPasswordHelper;
        $this->entityManager = $entityManager;
    }

    /**
     * Permet d'afficher / traiter le formulaire de demande de reset de mdp
     *
     * @Route("", name="app_forgot_password_request")
     */
    public function request(Request $request, MailerInterface $mailer): Response
    {
        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->processSendingPasswordResetUsername(
                $form->get('username')->getData(),
                $mailer
            );
        }

        return $this->render('reset_password/request.html.twig', [
            'requestForm' => $form->createView(),
        ]);
    }

    /**
     * Permet d'afficher la page de confirmation de la demande de reset
     *
     * @Route("/check-email", name="app_check_email")
     */
    public function checkEmail(): Response
    {
        // Si l'user n'existe pas on génère un faux token pour ne pas qu'on puisse savoir si un user existe ou non
        if (null === ($resetToken = $this->getTokenObjectFromSession())) {
            $resetToken = $this->resetPasswordHelper->generateFakeResetToken();
        }

        return $this->render('reset_password/check_email.html.twig', [
            'resetToken' => $resetToken,
        ]);
    }

    /**
     * Permet de reset le mdp
     *
     * @Route("/reset/{token}", name="app_reset_password")
     */
    public function reset(Request $request, UserPasswordHasherInterface $userPasswordHasher, string $token = null): Response
    {
        // On stock le token en session et on l'enlève de l'url s'il est présent
        if ($token) {
            $this->storeTokenInSession($token);
            return $this->redirectToRoute('app_reset_password');
        }

        $token = $this->getTokenFromSession();

        // Si le token est null on soulève une erreur
        if (null === $token) {
            throw $this->createNotFoundException('No reset password token found in the URL or in the session.');
        }

        try {
            // On valide le token et on récup l'user correspondant
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface $e) {
            $this->addFlash('error', sprintf(
                'There was a problem validating your reset request - %s',
                $e->getReason()
            ));

            return $this->redirectToRoute('app_forgot_password_request');
        }

        // Si le token est valide on crée le formulaire de pour le nouveau mdp
        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        // Si le formulaire est envoyé & valid on reset le mdp
        if ($form->isSubmitted() && $form->isValid()) {
            // On supprime la demande de reset de mdp
            $this->resetPasswordHelper->removeResetRequest($token);

            // On hash le mdp
            $encodedPassword = $userPasswordHasher->hashPassword(
                $user,
                $form->get('password')->getData()
            );

            // On set le mdp
            $user->setPassword($encodedPassword);
            $this->entityManager->flush();

            // On clean le session
            $this->cleanSessionAfterReset();

            // On affiche la page d'accueil
            return $this->redirectToRoute('homepage');
        }

        return $this->render('reset_password/reset.html.twig', [
            'resetForm' => $form->createView(),
        ]);
    }

    /**
     * Permet d'envoyer le mail de reset de mdp
     *
     * @param string $usernameFormData
     * @param MailerInterface $mailer
     * @return RedirectResponse
     *
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    private function processSendingPasswordResetUsername(string $usernameFormData, MailerInterface $mailer): RedirectResponse
    {
        // on récup l'user
        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'username' => $usernameFormData,
        ]);

        // S'il n'existe pas on redirige direct sur la route de confirmation de mail (on ne dit pas s'il existe)
        if (!$user) {
            return $this->redirectToRoute('app_check_email');
        }

        try {
            // On génère le token de reset
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface $e) {
            return $this->redirectToRoute('app_check_email');
        }

        // On prépare le mail
        $email = (new TemplatedEmail())
            ->from(new Address('mailer@jdevuono.com', 'Jdevuono'))
            ->to($user->getEmail())
            ->subject('Reinitialisation de votre mot de passe')
            ->htmlTemplate('reset_password/email.html.twig')
            ->context([
                'resetToken' => $resetToken,
            ])
        ;

        // On envoi le mail
        $mailer->send($email);

        // On stock le token en session
        $this->setTokenObjectInSession($resetToken);

        return $this->redirectToRoute('app_check_email');
    }
}
