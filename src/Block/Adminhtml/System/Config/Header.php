<?php

declare(strict_types=1);

namespace ControlAltDelete\ColissimoHyva\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template;
use Magento\Config\Block\System\Config\Form\Fieldset;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Header extends Fieldset
{
    private const TEMPLATE = 'ControlAltDelete_ColissimoHyva::system/config/header.phtml';

    public function render(AbstractElement $element): string
    {
        $this->setElement($element);

        $headerHtml = $this->getLayout()
            ->createBlock(Template::class)
            ->setTemplate(self::TEMPLATE)
            ->toHtml();

        return $headerHtml . parent::render($element);
    }
}
