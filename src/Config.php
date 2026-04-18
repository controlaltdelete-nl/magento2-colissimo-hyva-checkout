<?php

declare(strict_types=1);

namespace ControlAltDelete\ColissimoHyva;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    private const XML_PATH_API_KEY = 'controlaltdelete_colissimo_hyva/google_maps/api_key';
    private const XML_PATH_REGION = 'controlaltdelete_colissimo_hyva/google_maps/region';
    private const XML_PATH_STARTING_LATITUDE = 'controlaltdelete_colissimo_hyva/google_maps/starting_latitude';
    private const XML_PATH_STARTING_LONGITUDE = 'controlaltdelete_colissimo_hyva/google_maps/starting_longitude';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
    ) {}

    public function getGoogleMapsApiKey(): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_API_KEY);
    }

    public function getRegion(): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_REGION);
    }

    public function getStartingLatitude(): float
    {
        return (float)$this->scopeConfig->getValue(self::XML_PATH_STARTING_LATITUDE);
    }

    public function getStartingLongitude(): float
    {
        return (float)$this->scopeConfig->getValue(self::XML_PATH_STARTING_LONGITUDE);
    }
}
