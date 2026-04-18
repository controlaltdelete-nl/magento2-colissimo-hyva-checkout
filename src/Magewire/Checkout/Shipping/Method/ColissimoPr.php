<?php

declare(strict_types=1);

namespace ControlAltDelete\ColissimoHyva\Magewire\Checkout\Shipping\Method;

use ControlAltDelete\ColissimoHyva\Service\SelectedPickupPoint;
use ControlAltDelete\ColissimoHyva\Service\SelectedPickupPointFactory;
use Hyva\Checkout\Model\Magewire\Component\Evaluation\EvaluationResult;
use Hyva\Checkout\Model\Magewire\Component\EvaluationInterface;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultFactory;
use Magento\Checkout\Model\Session;
use Magewirephp\Magewire\Component;

class ColissimoPr extends Component implements EvaluationInterface
{
    protected $listeners = [
        'relay-point-picked' => 'refresh'
    ];

    public function __construct(
        private readonly Session $checkoutSession,
        private readonly SelectedPickupPointFactory $selectedPickupPointFactory,
    ) {}

    public function getSelectedPickupPoint(): ?SelectedPickupPoint
    {
        $lpcRelayInformation = $this->checkoutSession->getLpcRelayInformation();
        if (!$lpcRelayInformation) {
            return null;
        }

        return $this->selectedPickupPointFactory->create([
            'information' => $lpcRelayInformation,
        ]);
    }

    public function evaluateCompletion(EvaluationResultFactory $resultFactory): EvaluationResult
    {
        $shippingMethod = $this->checkoutSession->getQuote()->getShippingAddress()->getShippingMethod();

        if ($shippingMethod !== 'colissimo_pr') {
            return $resultFactory->createSuccess();
        }

        $relayInfo = $this->checkoutSession->getLpcRelayInformation();

        if (!empty($relayInfo['id'])) {
            return $resultFactory->createSuccess();
        }

        return $resultFactory->createErrorMessage()
            ->withMessage(__('Please select a pickup point before proceeding.')->render())
            ->withVisibilityDuration(5000)
            ->asError();
    }
}
