<?php

namespace App\Security;

use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user){}

    /**
     * Se déclenche une fois qu'on a authentifié l'user (username & mdp ok)
     *
     * @param UserInterface $user
     */
    public function checkPostAuth(UserInterface $user)
    {
        // Si le compte n'est pas déjà validé on soulève une exception
        if (!$user->isVerified()) {
            throw new CustomUserMessageAuthenticationException("Vous n'avez pas valider votre email ! " . '<a href="/verify/email/resend/'.urlencode($user->getId()).'">Renvoyer le mail de confirmation</a>');
        }
    }
}