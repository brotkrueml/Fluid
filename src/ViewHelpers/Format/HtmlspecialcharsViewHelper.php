<?php
declare(strict_types=1);
namespace TYPO3Fluid\Fluid\ViewHelpers\Format;

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Applies htmlspecialchars() escaping to a value
 *
 * @see http://www.php.net/manual/function.htmlspecialchars.php
 *
 * = Examples =
 *
 * <code title="default notation">
 * <f:format.htmlspecialchars>{text}</f:format.htmlspecialchars>
 * </code>
 * <output>
 * Text with & " ' < > * replaced by HTML entities (htmlspecialchars applied).
 * </output>
 *
 * <code title="inline notation">
 * {text -> f:format.htmlspecialchars(encoding: 'ISO-8859-1')}
 * </code>
 * <output>
 * Text with & " ' < > * replaced by HTML entities (htmlspecialchars applied).
 * </output>
 *
 * @api
 */
class HtmlspecialcharsViewHelper extends AbstractViewHelper
{

    /**
     * @var boolean
     */
    protected $escapeChildren = false;

    /**
     * Disable the output escaping interceptor so that the value is not htmlspecialchar'd twice
     *
     * @var boolean
     */
    protected $escapeOutput = false;

    /**
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('value', 'string', 'Value to format');
        $this->registerArgument('keepQuotes', 'boolean', 'If TRUE quotes will not be replaced (ENT_NOQUOTES)', false, false);
        $this->registerArgument('encoding', 'string', 'Encoding', false, 'UTF-8');
        $this->registerArgument('doubleEncode', 'boolean', 'If FALSE html entities will not be encoded', false, true);
    }

    /**
     * Escapes special characters with their escaped counterparts as needed using PHPs htmlspecialchars() function.
     *
     * @return mixed the altered string or unaltered non-string-representable value
     * @see http://www.php.net/manual/function.htmlspecialchars.php
     * @api
     */
    public function render()
    {
        $value = $this->arguments['value'];
        $keepQuotes = $this->arguments['keepQuotes'];
        $encoding = $this->arguments['encoding'];
        $doubleEncode = $this->arguments['doubleEncode'];
        if ($value === null) {
            $value = $this->renderChildren();
        }

        if (!is_string($value) && !(is_object($value) && method_exists($value, '__toString'))) {
            return $value;
        }
        $flags = $keepQuotes ? ENT_NOQUOTES : ENT_QUOTES;

        return htmlspecialchars($value, $flags, $encoding, $doubleEncode);
    }
}
