<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\View\Resolver;

use Zend\View\Helper\ViewModel as ViewModelHelper;
use Zend\View\Model\ModelInterface;
use Zend\View\Renderer\RendererInterface;

/**
 * Relative fallback resolver - resolves to view templates in a sub-path of the
 * currently set view model's template (if the current renderer has the `view_model` plugin set).
 *
 * This allows for usage of partial template paths such as `some/partial`, resolving to
 * `my/module/script/path/some/partial.phtml`, while rendering template `my/module/script/path/my-view`
 */
class RelativeFallbackResolver implements ResolverInterface
{
    const NS_SEPARATOR = '/';

    /**
     * @var ResolverInterface
     */
    private $resolver;

    /**
     * Constructor
     *
     * @param ResolverInterface $resolver
     */
    public function __construct(ResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve($name, RendererInterface $renderer = null)
    {
        // There should exists view model to get template name
        if (! is_callable(array($renderer, 'plugin'))) {
            return false;
        }

        // Try to get it from the same name space (folder)
        $helper = $renderer->plugin('view_model');

        if (! $helper instanceof ViewModelHelper) {
            return false;
        }

        $currentModel = $helper->getCurrent();

        if (! $currentModel instanceof ModelInterface) {
            return false;
        }

        $currentTemplate = $currentModel->getTemplate();
        $position        = strrpos($currentTemplate, self::NS_SEPARATOR);

        if ($position > 0) {
            return $this->resolver->resolve(
                substr($currentTemplate, 0, $position) . self::NS_SEPARATOR . $name,
                $renderer
            );
        }

        return false;
    }
}
