<?php
namespace Smichaelsen\Settings\ViewHelpers;

use Smichaelsen\Settings\Service\ConfigurationService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Fluid\Core\ViewHelper\Facets\CompilableInterface;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class GetValueViewHelper extends AbstractViewHelper implements CompilableInterface
{

    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('name', 'string', '', true);
    }

    public function render()
    {
        return self::renderStatic($this->arguments, $this->buildRenderChildrenClosure(), $this->renderingContext);
    }

    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $configuration = self::getAllConfiguration();
        if (!isset($configuration[$arguments['name']])) {
            return null;
        }
        return $configuration[$arguments['name']];
    }

    /**
     * @return array|null
     */
    protected static function getAllConfiguration()
    {
        static $allConfiguration = null;
        if ($allConfiguration === null) {
            $configurationService = GeneralUtility::makeInstance(ConfigurationService::class);
            $pid = (int) self::getTypoScriptFrontendController()->rootLine[0]['uid'];
            $allConfiguration = $configurationService->getAllConfiguration($pid);
        }
        return $allConfiguration;
    }

    /**
     * @return TypoScriptFrontendController
     */
    protected static function getTypoScriptFrontendController()
    {
        return $GLOBALS['TSFE'];
    }
}
