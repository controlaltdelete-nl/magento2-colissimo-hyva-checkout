<?php

declare(strict_types=1);

namespace ControlAltDelete\ColissimoHyva\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class TestGoogleMapsApiKeyButton extends Field
{
    private const API_KEY_INPUT_ID = 'carriers_lpc_group_google_maps_api_key';

    protected $_template = 'ControlAltDelete_ColissimoHyva::system/config/test-google-maps-api-key.phtml';

    public function render(AbstractElement $element): string
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        return parent::render($element);
    }

    public function getTestUrl(): string
    {
        return $this->getUrl('colissimohyva/googlemaps/testapikey');
    }

    public function getApiKeyInputId(): string
    {
        return self::API_KEY_INPUT_ID;
    }

    protected function _getElementHtml(AbstractElement $element): string
    {
        return $this->_toHtml();
    }
}
