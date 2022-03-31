<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\Security\Logout;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

/**
 * @deprecated Deprecated since Contao 4.13, to be removed in Contao 5.0; use
 *             the Symfony\Component\Security\Http\Event\LogoutEvent event instead
 */
class LogoutHandler implements LogoutHandlerInterface
{
    use TargetPathTrait;

    /**
     * @internal Do not inherit from this class; decorate the "contao.security.logout_handler" service instead
     */
    public function __construct()
    {
    }

    public function logout(Request $request, ?Response $response, TokenInterface $token): void
    {
        trigger_deprecation('contao/core-bundle', '4.13', 'Using the LogoutHandler has been deprecated an will no longer work in Contao 5. Use the Symfony\Component\Security\Http\Event\LogoutEvent instead.');
    }
}
