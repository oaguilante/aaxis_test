<?php

namespace App\Security;

use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\JsonResponse;

class AccessTokenHandler implements AccessTokenHandlerInterface
{
    public function getUserBadgeFrom(string $accessToken): UserBadge
    {
        if ($accessToken !== 'AdminAaxis:AdminAaxxis2018') {
        }

        return new UserBadge('AdminAaxis');
    }

    public function validateToken(string  $authorizationHeader): ?JsonResponse
    {
        if (!$authorizationHeader || $authorizationHeader !== 'Basic ' . base64_encode('AdminAaxis:AdminAaxxis2018')) {
            return new JsonResponse(['error' => 'Invalid authorization token.'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        return null;
    }
}