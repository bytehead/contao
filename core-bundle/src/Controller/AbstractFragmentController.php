<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\Controller;

use Contao\CoreBundle\Fragment\FragmentOptionsAwareInterface;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\FrontendTemplate;
use Contao\Model;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\Template;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\RequestStack;

abstract class AbstractFragmentController extends AbstractController implements FragmentOptionsAwareInterface
{
    /**
     * @var array
     */
    protected $options = [];

    public function setFragmentOptions(array $options): void
    {
        $this->options = $options;
    }

    /**
     * @return array<string>
     */
    public static function getSubscribedServices()
    {
        $services = parent::getSubscribedServices();

        $services['request_stack'] = RequestStack::class;
        $services['contao.routing.scope_matcher'] = ScopeMatcher::class;

        return $services;
    }

    protected function getPageModel(): ?PageModel
    {
        $request = $this->get('request_stack')->getCurrentRequest();

        if (null === $request || !$request->attributes->has('pageModel')) {
            return null;
        }

        $pageModel = $request->attributes->get('pageModel');

        if ($pageModel instanceof PageModel) {
            return $pageModel;
        }

        if (
            isset($GLOBALS['objPage'])
            && $GLOBALS['objPage'] instanceof PageModel
            && (int) $GLOBALS['objPage']->id === (int) $pageModel
        ) {
            return $GLOBALS['objPage'];
        }

        $this->initializeContaoFramework();

        /** @var PageModel $pageAdapter */
        $pageAdapter = $this->get('contao.framework')->getAdapter(PageModel::class);

        return $pageAdapter->findByPk((int) $pageModel);
    }

    /**
     * Creates a template by name or from the "customTpl" field of the model.
     */
    protected function createTemplate(Model $model, string $templateName): Template
    {
        if (isset($this->options['template'])) {
            $templateName = $this->options['template'];
        }

        if ($model->customTpl) {
            $request = $this->get('request_stack')->getCurrentRequest();

            // Use the custom template unless it is a back end request
            if (null === $request || !$this->get('contao.routing.scope_matcher')->isBackendRequest($request)) {
                $templateName = $model->customTpl;
            }
        }

        $template = $this->get('contao.framework')->createInstance(FrontendTemplate::class, [$templateName]);
        $template->setData($model->row());

        return $template;
    }

    /**
     * @param string|array $headline
     */
    protected function addHeadlineToTemplate(Template $template, $headline): void
    {
        $data = StringUtil::deserialize($headline);
        $template->headline = \is_array($data) ? $data['value'] : $data;
        $template->hl = \is_array($data) ? $data['unit'] : 'h1';
    }

    /**
     * @param string|array $cssID
     */
    protected function addCssAttributesToTemplate(Template $template, string $templateName, $cssID, array $classes = null): void
    {
        $data = StringUtil::deserialize($cssID, true);
        $template->class = trim($templateName.' '.($data[1] ?? ''));
        $template->cssID = !empty($data[0]) ? ' id="'.$data[0].'"' : '';

        if (!empty($classes)) {
            $template->class .= ' '.implode(' ', $classes);
        }
    }

    protected function addPropertiesToTemplate(Template $template, array $properties): void
    {
        foreach ($properties as $k => $v) {
            $template->{$k} = $v;
        }
    }

    protected function addSectionToTemplate(Template $template, string $section): void
    {
        $template->inColumn = $section;
    }

    /**
     * Returns the type from the class name.
     */
    protected function getType(): string
    {
        if (isset($this->options['type'])) {
            return $this->options['type'];
        }

        $className = ltrim(strrchr(static::class, '\\'), '\\');

        if ('Controller' === substr($className, -10)) {
            $className = substr($className, 0, -10);
        }

        return Container::underscore($className);
    }
}
