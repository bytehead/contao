<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\Controller\FrontendModule;

use Contao\Controller;
use Contao\ModuleModel;
use Contao\StringUtil;
use Contao\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RootPageDependentModulesController extends AbstractFrontendModuleController
{
    public function getResponse(Template $template, ModuleModel $model, Request $request): Response
    {
        if (!$pageModel = $this->getPageModel()) {
            return new Response('');
        }

        $modules = StringUtil::deserialize($model->rootPageDependentModules);

        if (empty($modules) || !\is_array($modules) || !\array_key_exists($pageModel->rootId, $modules)) {
            return new Response('');
        }

        $controller = $this->container->get('contao.framework')->getAdapter(Controller::class);
        $content = $controller->getFrontendModule($modules[$pageModel->rootId]);

        return new Response($content);
    }
}
