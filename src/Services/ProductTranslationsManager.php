<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2019 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace   Splash\Sylius\Services;

use Sylius\Component\Product\Model\ProductTranslationInterface;
use Sylius\Component\Product\Model\ProductOptionTranslationInterface;
use Sylius\Component\Product\Model\ProductOptionValueTranslationInterface;
use Sylius\Component\Product\Model\ProductVariantInterface;
use Sylius\Component\Product\Model\ProductOptionInterface;
use Sylius\Component\Product\Model\ProductOptionValueInterface;
use Sylius\Component\Locale\Model\LocaleInterface;
use Sylius\Component\Resource\Factory\Factory;

/**
 * Product Translations Manager
 * Manage Access to Products Translations
 */
class ProductTranslationsManager
{
    /**
     * @var Factory
     */
    protected $factory;

    /**
     * @var Factory
     */
    protected $options;

    /**
     * @var Factory
     */
    protected $values;
   
    /**
     * @var array
     */
    protected $config;

    /**
     * @var array
     */
    private $locales;

    /**
     * Service Constructor
     *
     * @param Factory $factory
     * @param array   $locales
     * @param array   $configuration
     */
    public function __construct(Factory $factory, Factory $options, Factory $values, array $locales, array $configuration)
    {
        //====================================================================//
        // Sylius Translations Factory
        $this->factory = $factory;
        //====================================================================//
        // Sylius Options Translations Factory
        $this->options = $options;
        //====================================================================//
        // Sylius Values Translations Factory
        $this->values = $values;
        //====================================================================//
        // Store List of Locales
        $this->locales = $locales;
        //====================================================================//
        // Store Bundle Configuration
        $this->config = $configuration;
    }

    /**
     * Get Array of Available Locale
     *
     * @param LocaleInterface $locale
     *
     * @return bool
     */
    public function getLocales(): array
    {
        return $this->locales;
    }

    /**
     * Check if Locale is Default Locale
     *
     * @param LocaleInterface $locale
     *
     * @return bool
     */
    public function isDefaultLocale(LocaleInterface $locale): bool
    {
        return ($locale->getCode() == $this->config["locale"]);
    }

    /**
     * Get Default Locale Iso Code
     *
     * @return string
     */
    public function getDefaultLocaleCode(): string
    {
        return $this->config["locale"];
    }
    
    /**
     * Get Locale Field Id Suffix
     *
     * @return string
     */
    public function getLocaleSuffix(LocaleInterface $locale): string
    {
        return $this->isDefaultLocale($locale) ? "" : "_" . $locale->getCode();
    }    

    /**
     * Decode Multilang FieldName with ISO Code
     *
     * @param LocaleInterface $locale    Sylius Locale Object
     * @param string          $fieldName Complete Field Name
     *
     * @return string Base Field Name or Empty String
     */
    public function fieldNameDecode(LocaleInterface $locale, $fieldName): string
    {
        //====================================================================//
        // Default Language => No code in FieldName
        if ($this->isDefaultLocale($locale)) {
            return $fieldName;
        }
        //====================================================================//
        // Other Languages => Check if Code is in FieldName
        if (false === strpos($fieldName, $locale->getCode())) {
            return "";
        }

        return substr($fieldName, 0, strlen($fieldName) - strlen($locale->getCode()) - 1);
    }

    /**
     * Get Translated Product String
     *
     * @param ProductVariantInterface $variant
     * @param LocaleInterface         $locale
     * @param type                    $baseFieldName
     *
     * @return string
     */
    public function getTranslated(ProductVariantInterface $variant, LocaleInterface $locale, $baseFieldName): string
    {
        $translations = $variant->getProduct()->getTranslations();

        if (isset($translations[$locale->getCode()])) {
            return (string) $translations[$locale->getCode()]->{ "get".ucfirst($baseFieldName) }();
        }

        return "";
    }

    /**
     * Set Translated Product String
     *
     * @param ProductVariantInterface $variant
     * @param LocaleInterface         $locale
     * @param string                  $baseFieldName
     * @param string                  $fieldData
     *
     * @return bool
     */
    public function setTranslated(ProductVariantInterface $variant, LocaleInterface $locale, string $baseFieldName, string $fieldData): bool
    {
        //====================================================================//
        // Load Product Translations
        $isoCode = $locale->getCode();
        $translations = $variant->getProduct()->getTranslations();
        //====================================================================//
        // Add Translation if No Exists
        if (!isset($translations[$isoCode])) {
            $translations[$isoCode] = $this->createTranslation($variant, $locale);
        }
        //====================================================================//
        // Compare Values
        $current = $translations[$locale->getCode()]->{ "get".ucfirst($baseFieldName) }();
        if ($current == $fieldData) {
            return false;
        }
        //====================================================================//
        // Write Value to Translation
        $translations[$isoCode]->{ "set".ucfirst($baseFieldName) }($fieldData);

        return true;
    }

    /**
     * Set Translated Product Option String
     *
     * @param ProductVariantInterface $option
     * @param LocaleInterface         $locale
     * @param string                  $baseFieldName
     * @param string                  $fieldData
     *
     * @return bool
     */
    public function setOptionTranslation(ProductOptionInterface $option, LocaleInterface $locale, string $fieldData): bool
    {
        //====================================================================//
        // Load Product Translations
        $isoCode = $locale->getCode();
        $translations = $option->getTranslations();
        //====================================================================//
        // Add Translation if No Exists
        if (!isset($translations[$isoCode])) {
            $translations[$isoCode] = $this->createOptionTranslation($option, $locale);
        }
        //====================================================================//
        // Compare Values
        $current = $translations[$locale->getCode()]->getName();
        if ($current == $fieldData) {
            return false;
        }
        //====================================================================//
        // Write Value to Translation
        $translations[$isoCode]->setName($fieldData);

        return true;
    }
    
    /**
     * Set Translated Product Option VAlue String
     *
     * @param ProductVariantInterface $optionValue
     * @param LocaleInterface         $locale
     * @param string                  $baseFieldName
     * @param string                  $fieldData
     *
     * @return bool
     */
    public function setOptionValueTranslation(ProductOptionValueInterface $optionValue, LocaleInterface $locale, string $fieldData): bool
    {
        //====================================================================//
        // Load Product Translations
        $isoCode = $locale->getCode();
        $translations = $optionValue->getTranslations();
        //====================================================================//
        // Add Translation if No Exists
        if (!isset($translations[$isoCode])) {
            $translations[$isoCode] = $this->createOptionValueTranslation($optionValue, $locale);
        }
        //====================================================================//
        // Compare Values
        $current = $translations[$locale->getCode()]->getValue();
        if ($current == $fieldData) {
            return false;
        }
        //====================================================================//
        // Write Value to Translation
        $translations[$isoCode]->setValue($fieldData);

        return true;
    }    
    
    /**
     * Create New Product Translation
     *
     * @param ProductVariantInterface $variant
     * @param LocaleInterface         $locale
     *
     * @return ProductTranslationInterface
     */
    private function createTranslation(ProductVariantInterface $variant, LocaleInterface $locale): ProductTranslationInterface
    {
        //====================================================================//
        // Create New Translation
        $translation = $this->factory->createNew();
        $translation->setLocale($locale->getCode());
        $translation->setTranslatable($variant->getProduct());
        $translation->setName($variant->getCode());
        $translation->setSlug(uniqid($variant->getCode()));

        return $translation;
    }
    
    /**
     * Create New Product Option Translation
     *
     * @param ProductOptionInterface $option
     * @param LocaleInterface         $locale
     *
     * @return ProductOptionTranslationInterface
     */
    private function createOptionTranslation(ProductOptionInterface $option, LocaleInterface $locale): ProductOptionTranslationInterface
    {
        //====================================================================//
        // Create New Option Translation
        $translation = $this->options->createNew();
        $translation->setLocale($locale->getCode());
        $translation->setTranslatable($option);

        return $translation;
    }  
    
    /**
     * Create New Product Option Translation
     *
     * @param ProductOptionInterface $optionValue
     * @param LocaleInterface         $locale
     *
     * @return ProductOptionTranslationInterface
     */
    private function createOptionValueTranslation(ProductOptionValueInterface $optionValue, LocaleInterface $locale): ProductOptionValueTranslationInterface
    {
        //====================================================================//
        // Create New Option Value Translation
        $translation = $this->values->createNew();
        $translation->setLocale($locale->getCode());
        $translation->setTranslatable($optionValue);

        return $translation;
    }        
}