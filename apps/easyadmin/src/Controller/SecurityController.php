<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class SecurityController extends AbstractController
{
    #[Route('/login', name: 'login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        return $this->render('security/login.html.twig', [
            'accounts' => ['admin' => 'does everything', 'clerk' => 'can not delete a thing'],
            'csrf_token_intention' => 'authenticate',
            'error' => $authenticationUtils->getLastAuthenticationError(),
            'last_username' => $authenticationUtils->getLastUsername(),
            'page_title' => '🐘 Wax Elephant Records',
            // Absolute: Symfony adds the base path to what starts with a slash
            'target_path' => $this->generateUrl('admin', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);
    }

    #[Route('/logout', name: 'logout')]
    public function logout(): never
    {
        throw new \LogicException('The firewall answers this route.');
    }
}
