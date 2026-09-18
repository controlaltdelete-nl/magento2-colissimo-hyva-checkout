<?php

declare(strict_types=1);

namespace ControlAltDelete\ColissimoHyva\Test\Integration\Magewire\Checkout\Shipping;

use ControlAltDelete\ColissimoHyva\Magewire\Checkout\Shipping\RelayPicker;
use ControlAltDelete\ColissimoHyva\Test\Integration\Fake\FakeGetLocationFromGoogleMaps;
use ControlAltDelete\ColissimoHyva\Test\Integration\Fake\FakeRelaysApi;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[AppArea('frontend')]
class RelayPickerTest extends TestCase
{
    private const COLISSIMO_CONFIG = [
        'lpc_advanced/lpc_general/connectionMode' => 'api',
        'lpc_advanced/lpc_general/api_key' => 'colissimo-test-api-key',
    ];

    private const NIMES_ADDRESS_COMPONENTS = [
        ['longText' => '30000', 'shortText' => '30000', 'types' => ['postal_code']],
        ['longText' => 'Nîmes', 'shortText' => 'Nîmes', 'types' => ['locality', 'political']],
        ['longText' => 'France', 'shortText' => 'FR', 'types' => ['country', 'political']],
    ];

    private Session $checkoutSession;

    protected function setUp(): void
    {
        $this->checkoutSession = Bootstrap::getObjectManager()->get(Session::class);
        $this->checkoutSession->unsLpcRelayInformation();
        $this->checkoutSession->getQuote()->getShippingAddress()->setCountryId('NL');

        $scopeConfig = Bootstrap::getObjectManager()->get(MutableScopeConfigInterface::class);
        foreach (self::COLISSIMO_CONFIG as $path => $value) {
            $scopeConfig->setValue($path, $value);
            $scopeConfig->setValue($path, $value, ScopeInterface::SCOPE_STORE, 'default');
        }
    }

    protected function tearDown(): void
    {
        $scopeConfig = Bootstrap::getObjectManager()->get(MutableScopeConfigInterface::class);
        foreach (array_keys(self::COLISSIMO_CONFIG) as $path) {
            $scopeConfig->setValue($path, null, ScopeInterface::SCOPE_STORE, 'default');
            $scopeConfig->setValue($path, null);
        }
    }

    #[Test]
    public function itSearchesPickupPointsInTheCountryOfTheSearchedAddress(): void
    {
        $relaysApi = new FakeRelaysApi();
        $relayPicker = $this->createRelayPicker($relaysApi, new FakeGetLocationFromGoogleMaps([]));

        $relayPicker->getPickupPointsForLatlng(43.8367, 4.3601, self::NIMES_ADDRESS_COMPONENTS);

        $this->assertSame('FR', $relaysApi->getRequests()[0]['countryCode']);
        $this->assertSame('30000', $relaysApi->getRequests()[0]['zipCode']);
        $this->assertSame('Nîmes', $relaysApi->getRequests()[0]['city']);
    }

    #[Test]
    public function itSendsThePostcodeInTheFormatThatColissimoAccepts(): void
    {
        $relaysApi = new FakeRelaysApi();
        $relayPicker = $this->createRelayPicker($relaysApi, new FakeGetLocationFromGoogleMaps([]));

        $relayPicker->getPickupPointsForLatlng(53.1554, 4.8562, [
            ['longText' => '1795 AD', 'shortText' => '1795 AD', 'types' => ['postal_code']],
            ['longText' => 'De Cocksdorp', 'shortText' => 'De Cocksdorp', 'types' => ['locality', 'political']],
            ['longText' => 'Netherlands', 'shortText' => 'NL', 'types' => ['country', 'political']],
        ]);

        $this->assertSame('1795AD', $relaysApi->getRequests()[0]['zipCode']);
        $this->assertSame('NL', $relaysApi->getRequests()[0]['countryCode']);
    }

    #[Test]
    public function itExplainsThatThePostcodeIsInvalidWhenColissimoRejectsThePostcode(): void
    {
        $relayPicker = $this->createRelayPicker(
            new FakeRelaysApi([], 144, 'Code postal incorrect format non respecté'),
            new FakeGetLocationFromGoogleMaps([])
        );

        $relayPicker->getPickupPointsForLatlng(43.8367, 4.3601, self::NIMES_ADDRESS_COMPONENTS);

        $this->assertSame(
            ['This postcode is not valid for the selected country. Please check the postcode and try again.'],
            array_map('strval', $relayPicker->errors)
        );
    }

    #[Test]
    public function itExplainsThatColissimoIsNotAvailableWhenTheCountryIsNotEligible(): void
    {
        $relayPicker = $this->createRelayPicker(
            new FakeRelaysApi([], 146, "Pays n'est pas éligible à Colissimo Europe"),
            new FakeGetLocationFromGoogleMaps([])
        );

        $relayPicker->getPickupPointsForLatlng(43.8367, 4.3601, self::NIMES_ADDRESS_COMPONENTS);

        $this->assertSame(
            ['Colissimo pickup points are not available in this country.'],
            array_map('strval', $relayPicker->errors)
        );
    }

    #[Test]
    public function itShowsAGeneralErrorForOtherColissimoErrors(): void
    {
        $relayPicker = $this->createRelayPicker(
            new FakeRelaysApi([], 999, 'Erreur inconnue'),
            new FakeGetLocationFromGoogleMaps([])
        );

        $relayPicker->getPickupPointsForLatlng(43.8367, 4.3601, self::NIMES_ADDRESS_COMPONENTS);

        $this->assertSame(
            ['An error occurred while fetching pickup points. Please try again later.'],
            array_map('strval', $relayPicker->errors)
        );
    }

    #[Test]
    public function itUsesTheShippingAddressCountryWhenTheSearchedAddressHasNoCountry(): void
    {
        $relaysApi = new FakeRelaysApi();
        $relayPicker = $this->createRelayPicker($relaysApi, new FakeGetLocationFromGoogleMaps([]));

        $relayPicker->getPickupPointsForLatlng(43.8367, 4.3601, array_slice(self::NIMES_ADDRESS_COMPONENTS, 0, 2));

        $this->assertSame('NL', $relaysApi->getRequests()[0]['countryCode']);
    }

    #[Test]
    public function itUsesTheCountryOfTheGeolocatedPositionWhenSearchingByCurrentLocation(): void
    {
        $relaysApi = new FakeRelaysApi();
        $relayPicker = $this->createRelayPicker(
            $relaysApi,
            new FakeGetLocationFromGoogleMaps(['city' => 'Nîmes', 'postalCode' => '30000', 'countryCode' => 'FR'])
        );

        $relayPicker->getPickupPointsForLatlng(43.8367, 4.3601);

        $this->assertSame('FR', $relaysApi->getRequests()[0]['countryCode']);
        $this->assertSame('30000', $relaysApi->getRequests()[0]['zipCode']);
    }

    #[Test]
    public function itAsksForAMoreSpecificAddressWhenNoPostcodeCanBeFound(): void
    {
        $relaysApi = new FakeRelaysApi();
        $relayPicker = $this->createRelayPicker(
            $relaysApi,
            new FakeGetLocationFromGoogleMaps(['city' => 'Nîmes', 'postalCode' => null, 'countryCode' => 'FR'])
        );

        $relayPicker->getPickupPointsForLatlng(43.8367, 4.3601, [self::NIMES_ADDRESS_COMPONENTS[1]]);

        $this->assertSame(
            ['We could not find a postcode for this location. Please enter a more specific address.'],
            array_map('strval', $relayPicker->errors)
        );
        $this->assertSame([], $relaysApi->getRequests());
    }

    #[Test]
    public function itLimitsTheAddressSearchToTheShippingCountry(): void
    {
        $relayPicker = $this->createRelayPicker(new FakeRelaysApi(), new FakeGetLocationFromGoogleMaps([]));

        $relayPicker->mount();

        $this->assertSame('NL', $relayPicker->searchCountryCode);
    }

    #[Test]
    public function itLimitsTheAddressSearchToTheDefaultCountryWithoutAShippingCountry(): void
    {
        $this->checkoutSession->getQuote()->getShippingAddress()->setCountryId(null);
        $relayPicker = $this->createRelayPicker(new FakeRelaysApi(), new FakeGetLocationFromGoogleMaps([]));

        $relayPicker->mount();

        $this->assertSame($relayPicker->getDefaultCountry(), $relayPicker->searchCountryCode);
    }

    #[Test]
    public function itUpdatesTheAddressSearchCountryWhenTheShippingAddressChanges(): void
    {
        $relayPicker = $this->createRelayPicker(new FakeRelaysApi(), new FakeGetLocationFromGoogleMaps([]));
        $relayPicker->mount();
        $this->checkoutSession->getQuote()->getShippingAddress()->setCountryId('BE');

        $relayPicker->refreshPickupPointsWhenShippingAddressChanged();

        $this->assertSame('BE', $relayPicker->searchCountryCode);
    }

    #[Test]
    public function itOpensThePickerWhenColissimoPickupIsSelectedWithoutAPickupPoint(): void
    {
        $relayPicker = $this->createRelayPicker(new FakeRelaysApi(), new FakeGetLocationFromGoogleMaps([]));

        $relayPicker->openPickerWhenNoPickupPointIsSelected(['code' => 'colissimo_pr']);

        $this->assertSame([['event' => 'open-relay-finder-popup', 'data' => null]], $relayPicker->getBrowserEvents());
    }

    #[Test]
    public function itDoesNotOpenThePickerWhenAnotherShippingMethodIsSelected(): void
    {
        $relayPicker = $this->createRelayPicker(new FakeRelaysApi(), new FakeGetLocationFromGoogleMaps([]));

        $relayPicker->openPickerWhenNoPickupPointIsSelected(['code' => 'flatrate_flatrate']);

        $this->assertSame([], $relayPicker->getBrowserEvents());
    }

    #[Test]
    public function itDoesNotOpenThePickerWhenAPickupPointIsAlreadySelected(): void
    {
        $this->checkoutSession->setLpcRelayInformation(['id' => '757170']);
        $relayPicker = $this->createRelayPicker(new FakeRelaysApi(), new FakeGetLocationFromGoogleMaps([]));

        $relayPicker->openPickerWhenNoPickupPointIsSelected(['code' => 'colissimo_pr']);

        $this->assertSame([], $relayPicker->getBrowserEvents());
    }

    private function createRelayPicker(
        FakeRelaysApi $relaysApi,
        FakeGetLocationFromGoogleMaps $getLocationFromGoogleMaps
    ): RelayPicker {
        return Bootstrap::getObjectManager()->create(RelayPicker::class, [
            'relaysApi' => $relaysApi,
            'getLocationFromGoogleMaps' => $getLocationFromGoogleMaps,
        ]);
    }
}
