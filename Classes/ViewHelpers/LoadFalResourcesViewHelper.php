<?php
namespace Smichaelsen\Settings\ViewHelpers;

use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Fluid\Core\ViewHelper\Facets\CompilableInterface;

class LoadFalResourcesViewHelper extends AbstractViewHelper implements CompilableInterface
{

    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('field', 'string', '', true);
        $this->registerArgument('as', 'string', '', true);
    }

    public function render()
    {
        return self::renderStatic($this->arguments, $this->buildRenderChildrenClosure(), $this->renderingContext);
    }

    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $fileReferences = self::getFileReferences('tx_settings_form', $arguments['field'], 1);
        $templateVariableContainer = $renderingContext->getTemplateVariableContainer();

        $templateVariableContainer->add($arguments['as'], $fileReferences);
        $output = $renderChildrenClosure();
        $templateVariableContainer->remove($arguments['as']);

        return $output;
    }

    /**
     * Fetch a fileReference from the file repository
     *
     * @param string $table name of the table to get the file reference for
     * @param string $field name of the field referencing a file
     * @param integer $uid uid of the related record
     * @return array
     */
    protected static function getFileReferences($table, $field, $uid)
    {
        return GeneralUtility::makeInstance(FileRepository::class)->findByRelation($table, $field, $uid);
    }
}
