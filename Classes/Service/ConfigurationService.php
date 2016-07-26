<?php
namespace Smichaelsen\Confengine\Service;

use TYPO3\CMS\Core\Registry;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ConfigurationService
{

    const REGISTRY_NAMESPACE = 'Smichaelsen\\Confengine';

    /**
     * @return array
     */
    public function getAllConfiguration()
    {
        $configuration = [];
        foreach (array_keys($GLOBALS['TCA']['tx_confengine_form']['columns']) as $columnKey) {
            $configuration[$columnKey] = $this->getRegistry()->get(self::REGISTRY_NAMESPACE, $columnKey);
        }
        return $configuration;
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        $this->getRegistry()->set(self::REGISTRY_NAMESPACE, $key, $value);
    }

    /**
     * @return Registry
     */
    protected function getRegistry()
    {
        static $registry;
        if (!$registry instanceof Registry) {
            $registry = GeneralUtility::makeInstance(Registry::class);
        }
        return $registry;
    }

}
