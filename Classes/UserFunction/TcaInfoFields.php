<?php
namespace Smichaelsen\Confengine\UserFunction;

use TYPO3\CMS\Lang\LanguageService;

class TcaInfoFields
{

    /**
     * @param $parameters
     * @return string
     */
    public function availablePlaceholders($parameters)
    {
        if (!count($parameters['parameters'])) {
            return '';
        }
        $content = '<dl>';
        foreach ($parameters['parameters'] as $placeholder => $description) {
            $content .= '<dt>[[' . $placeholder . ']]</dt>';
            $content .= '<dd>' . $this->getLanguageService()->sL($description) . '</dd>';
        }
        $content .= '</dl>';
        return $content;
    }

    /**
     * @return LanguageService
     */
    protected function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }

}
