<?php
namespace Smichaelsen\Confengine\Service;

use TYPO3\CMS\Core\Registry;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ConfigurationService implements SingletonInterface
{

    const REGISTRY_NAMESPACE = 'Smichaelsen\\Confengine';

    /**
     * @return array
     */
    public function getAllConfiguration()
    {
        $configuration = [];
        foreach ($GLOBALS['TCA']['tx_confengine_form']['columns'] as $columnKey => $columnConfiguration) {
            $value = $this->getRegistry()->get(self::REGISTRY_NAMESPACE, $columnKey);
            $isMultiValueField = in_array($columnConfiguration['config']['type'], ['select', 'group']);
            if ($isMultiValueField && empty($value)) {
                $value = [];
            }
            $configuration[$columnKey] = $value;
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
