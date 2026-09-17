<?php

declare(strict_types=1);

namespace ControlAltDelete\ColissimoHyva\Magewire\Checkout\Shipping;

use ControlAltDelete\ColissimoHyva\Block\Checkout\Shipping\RelayPoint;
use ControlAltDelete\ColissimoHyva\Config;
use ControlAltDelete\ColissimoHyva\Service\GetLocationFromGoogleMaps;
use Exception;
use Hyva\Checkout\ViewModel\Checkout\Shipping\MethodList;
use LaPoste\Colissimo\Model\RelaysWebservice\GenerateRelaysPayload;
use LaPoste\Colissimo\Model\RelaysWebservice\RelaysApi;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\LayoutInterface;
use Magento\Quote\Model\Cart\ShippingMethod;
use Magewirephp\Magewire\Component;
use Psr\Log\LoggerInterface;

class RelayPicker extends Component
{
    private const COLISSIMO_PICKUP_METHOD_CODE = 'colissimo_pr';

    public array $pickupPoints = [];
    public array $renderedPickupPoints = [];

    public float $price = 0.0;
    public array $errors = [];
    public ?string $selectedPickupPointId = null;
    public ?string $pickupPointsShippingAddress = null;

    protected $listeners = [
        'shipping_address_saved' => 'refreshPickupPointsWhenShippingAddressChanged',
        'customer_shipping_address_saved' => 'refreshPickupPointsWhenShippingAddressChanged',
        'shipping_method_selected' => 'openPickerWhenNoPickupPointIsSelected',
    ];

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly Session $checkoutSession,
        private readonly RelaysApi $relaysApi,
        private readonly GenerateRelaysPayload $generateRelaysPayload,
        private readonly LayoutInterface $layout,
        private readonly MethodList $shippingMethodList,
        private readonly GetLocationFromGoogleMaps $getLocationFromGoogleMaps,
        private readonly Config $config,
        private readonly ScopeConfigInterface $scopeConfig,
    ) {}

    public function getDefaultCountry(): string
    {
        return $this->scopeConfig->getValue('general/country/default');
    }

    public function getGoogleMapsApiKey(): string
    {
        return $this->config->getGoogleMapsApiKey();
    }

    public function getPickupPoints(): void
    {
        $quote = $this->checkoutSession->getQuote();
        $shippingAddress = $quote->getShippingAddress();
        $postalCode = (string)$shippingAddress->getPostcode();
        $city = (string)$shippingAddress->getCity();
        $address = implode(' ', $shippingAddress->getStreet());

        if ($postalCode === '' || $city === '' || $address === '') {
            return;
        }

        $this->fetchPickupPoints($postalCode, $city, $address);
    }

    public function getPickupPointsForLatlng(float $latitude, float $longitude, ?array $addressComponents = null): void
    {
        $city = null;
        $postalCode = null;
        $address = null;
        $number = null;
        $street = null;
        $countryCode = null;

        if ($addressComponents !== null) {
            $postalCode = $this->getFromAddressComponents($addressComponents, 'postal_code');
            $city = $this->getFromAddressComponents($addressComponents, 'locality');
            $number = $this->getFromAddressComponents($addressComponents, 'street_number');
            $street = $this->getFromAddressComponents($addressComponents, 'route');
            $countryCode = $this->getFromAddressComponents($addressComponents, 'country', 'shortText');
        }

        if ($number && $street) {
            $address = $number . ' ' . $street;
        }

        if ($postalCode === null || $city === null) {
            try {
                $location = $this->getLocationFromGoogleMaps->byLatitudeLongitude($this->getShippingCountryCode(), $latitude, $longitude);
            } catch (Exception $exception) {
                $this->logger->error($exception->getMessage(), $exception->getTrace());
                $this->errors = [__('An error occurred while fetching pickup points. Please try again later.')];
                return;
            }

            $postalCode ??= $location['postalCode'];
            $city ??= $location['city'];
            $countryCode ??= $location['countryCode'];
        }

        if ($postalCode === null || $city === null) {
            $this->errors = [__('We could not find a postcode for this location. Please enter a more specific address.')];
            return;
        }

        $this->fetchPickupPoints($postalCode, $city, $address, $countryCode);
    }

    public function refreshPickupPointsWhenShippingAddressChanged(): void
    {
        if ($this->pickupPointsShippingAddress === null
            || $this->pickupPointsShippingAddress === $this->getShippingAddressFingerprint()) {
            return;
        }

        $this->pickupPoints = [];
        $this->renderedPickupPoints = [];
        $this->errors = [];
        $this->pickupPointsShippingAddress = null;

        $this->getPickupPoints();
    }

    public function openPickerWhenNoPickupPointIsSelected(array $shippingMethod): void
    {
        if (($shippingMethod['code'] ?? null) !== self::COLISSIMO_PICKUP_METHOD_CODE) {
            return;
        }

        if (!empty($this->checkoutSession->getLpcRelayInformation()['id'])) {
            return;
        }

        $this->dispatchBrowserEvent('open-relay-finder-popup');
    }

    public function getStartingLatitude(): float
    {
        return $this->config->getStartingLatitude();
    }

    public function getStartingLongitude(): float
    {
        return $this->config->getStartingLongitude();
    }

    public function initializeSelectedPickupPoint(): void
    {
        $relayInfo = $this->checkoutSession->getLpcRelayInformation();
        $this->selectedPickupPointId = $relayInfo['id'] ?? null;
    }

    public function mount(): void
    {
        $this->initializeSelectedPickupPoint();
    }

    public function selectPickupPoint(string $identifiant): void
    {
        $relayPoint = null;

        foreach ($this->pickupPoints as $point) {
            $pointId = is_array($point) ? ($point['identifiant'] ?? null) : ($point->identifiant ?? null);

            if ($pointId == $identifiant) {
                $relayPoint = is_array($point) ? (object) $point : $point;
                break;
            }
        }

        if (!$relayPoint) {
            return;
        }

        $relayInformation = [
            'id' => $relayPoint->identifiant,
            'name' => $relayPoint->nom,
            'address' => trim($relayPoint->adresse1 . PHP_EOL . $relayPoint->adresse2 . PHP_EOL . $relayPoint->adresse3),
            'post_code' => $relayPoint->codePostal,
            'city' => $relayPoint->localite,
            'type' => $relayPoint->typeDePoint,
            'country' => $relayPoint->codePays,
        ];

        $this->checkoutSession->setLpcRelayInformation($relayInformation);
        $this->selectedPickupPointId = $identifiant;
        $this->dispatchBrowserEvent('close-relay-finder-popup');
        $this->emit('relay-point-picked');
    }

    private function fetchPickupPoints(string $postalCode, string $city, ?string $address = null, ?string $countryCode = null): void
    {
        if (!$postalCode || !$city) {
            ['postalCode' => $postalCode, 'city' => $city] = $this->getDefaultLocation();
        }

        $countryCode = $countryCode ?: $this->getShippingCountryCode();

        try {
            $this->renderedPickupPoints = [];

            $this->generateRelaysPayload
                ->withCredentials()
                ->withAddress([
                    'address' => $address,
                    'zipCode' => $postalCode,
                    'city' => $city,
                    'countryCode' => $countryCode,
                ])
                ->withShippingDate()
                ->withOptionInter()
                ->checkConsistency();

            $result = $this->relaysApi->getRelays($this->generateRelaysPayload->assemble());

            if ($result->return->errorCode != 0) {
                throw new LocalizedException(__('Error fetching pickup points: %1', $result->return->errorMessage));
            }

            $this->pickupPoints = $result->return->listePointRetraitAcheminement ?? [];
            $this->pickupPointsShippingAddress = $this->getShippingAddressFingerprint();
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage(), $e->getTrace());
            $this->errors = [__('An error occurred while fetching pickup points. Please try again later.')];
            return;
        }

        $this->price = $this->getPrice();

        foreach ($this->pickupPoints as $pickupPoint) {
            $id = 'colissimo.relay.point.' . $pickupPoint->identifiant;
            $this->renderedPickupPoints[$id] = [
                'identifiant' => $pickupPoint->identifiant,
                'coordGeolocalisationLatitude' => $pickupPoint->coordGeolocalisationLatitude,
                'coordGeolocalisationLongitude' => $pickupPoint->coordGeolocalisationLongitude,
                'html' => $this->layout->createBlock(
                    RelayPoint::class,
                    $id,
                    [
                        'data' => [
                            'pickupPoint' => $pickupPoint,
                            'price' => $this->price,
                        ],
                    ]
                )->toHtml(),
            ];
        }
    }

    private function getDefaultLocation(): array
    {
        $quote = $this->checkoutSession->getQuote();
        $shippingAddress = $quote->getShippingAddress();

        if ($shippingAddress->getEntityId()) {
            return [
                'postalCode' => $shippingAddress->getPostcode(),
                'city' => $shippingAddress->getCity(),
            ];
        }

        return [
            'postalCode' => null,
            'city' => null,
        ];
    }

    private function getShippingAddressFingerprint(): string
    {
        $shippingAddress = $this->checkoutSession->getQuote()->getShippingAddress();

        return implode('|', [
            $shippingAddress->getCountryId(),
            $shippingAddress->getPostcode(),
            $shippingAddress->getCity(),
            implode(' ', $shippingAddress->getStreet()),
        ]);
    }

    private function getShippingCountryCode(): string
    {
        return $this->checkoutSession->getQuote()->getShippingAddress()->getCountryId() ?: $this->getDefaultCountry();
    }

    private function getFromAddressComponents(array $addressComponents, string $type, string $textKey = 'longText'): ?string
    {
        foreach ($addressComponents as $component) {
            if (in_array($type, $component['types'])) {
                return $component[$textKey] ?? null;
            }
        }

        return null;
    }

    private function getPrice(): float
    {
        /** @var ShippingMethod $method */
        foreach ($this->shippingMethodList->getList() ?? [] as $method) {
            if ($method->getCarrierCode() != 'colissimo' || $method->getMethodCode() != 'pr') {
                continue;
            }

            return $this->shippingMethodList->getMethodPrice($method);
        }

        return 0.0;
    }
}
