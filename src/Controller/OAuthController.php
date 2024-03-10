<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class OAuthController extends AbstractController
{
    private const DISCORD_ENDPOINT = 'https://discord.com/api/oauth2/authorize';
    /**
     * @Route("/oauth/discord", name="oauth_discord", methods={"GET"})
     */
    public function loginWithDiscord(CsrfTokenManagerInterface $csrfTokenManager, UrlGeneratorInterface $urlGenerator): RedirectResponse
    {
        $redirectUrl = $urlGenerator->generate(
            'app_login',
            ['discord-oauth-provider' => 1],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $queryParams = http_build_query([
            'client_id' => $this->getParameter('app.discord_client_id'),
            'prompt' => 'consent',
            'redirect_uri' => $redirectUrl,
            'response_type' => 'code',
            'scope' => 'identify email guilds guilds.join',
            'state' => $csrfTokenManager->getToken('oauth-discord')->getValue(),
        ]);

        return new RedirectResponse(self::DISCORD_ENDPOINT . '?' . $queryParams);
    }
}
