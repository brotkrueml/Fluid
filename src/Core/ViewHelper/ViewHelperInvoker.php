<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\Core\ViewHelper;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Class ViewHelperInvoker
 *
 * Class which is responsible for calling the render methods
 * on ViewHelpers, and this alone.
 *
 * Can be replaced via the ViewHelperResolver if the system
 * that implements Fluid requires special handling of classes.
 * This includes for example when you want to validate arguments
 * differently, wish to use another ViewHelper initialization
 * process, or wish to store instances of ViewHelpers to reuse
 * as if they were Singletons.
 *
 * To override the instantiation process and class name resolving,
 * see ViewHelperResolver. This particular class should only be
 * responsible for invoking the render method of a ViewHelper
 * using the properties available in the node.
 */
class ViewHelperInvoker
{

    /**
     * Invoke the ViewHelper described by the ViewHelperNode, the properties
     * of which will already have been filled by the ViewHelperResolver.
     *
     * @param string|ViewHelperInterface $viewHelperClassNameOrInstance
     * @param array $arguments
     * @param RenderingContextInterface $renderingContext
     * @param null|\Closure $renderChildrenClosure
     * @return mixed
     */
    public function invoke($viewHelperClassNameOrInstance, array $arguments, RenderingContextInterface $renderingContext, \Closure $renderChildrenClosure = null)
    {
        $viewHelperResolver = $renderingContext->getViewHelperResolver();
        if ($viewHelperClassNameOrInstance instanceof ViewHelperInterface) {
            $viewHelper = $viewHelperClassNameOrInstance;
        } else {
            $viewHelper = $viewHelperResolver->createViewHelperInstanceFromClassName($viewHelperClassNameOrInstance);
        }
        $expectedViewHelperArguments = $viewHelperResolver->getArgumentDefinitionsForViewHelper($viewHelper);
        // Rendering process
        $evaluatedArguments = [];
        $undeclaredArguments = [];

        try {
            foreach ($expectedViewHelperArguments as $argumentName => $argumentDefinition) {
                $argumentValue = $arguments[$argumentName] ?? $argumentDefinition->getDefaultValue();
                $evaluatedArguments[$argumentName] = $argumentValue instanceof NodeInterface ? $argumentValue->evaluate($renderingContext) : $argumentValue;
            }
            foreach ($arguments as $argumentName => $argumentValue) {
                if (!isset($evaluatedArguments[$argumentName])) {
                    $undeclaredArguments[$argumentName] = $argumentValue instanceof NodeInterface ? $argumentValue->evaluate($renderingContext) : $argumentValue;
                }
            }

            if ($renderChildrenClosure !== null) {
                $viewHelper->setRenderChildrenClosure($renderChildrenClosure);
            }
            $viewHelper->setRenderingContext($renderingContext);
            $viewHelper->setArguments($evaluatedArguments);
            $viewHelper->handleAdditionalArguments($undeclaredArguments);
            return $viewHelper->initializeArgumentsAndRender();
        } catch (Exception $error) {
            return $renderingContext->getErrorHandler()->handleViewHelperError($error);
        }
    }
}
