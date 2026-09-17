import {expect, Page, test} from '@playwright/test';
import LoadCheckout from '../actions/LoadCheckout';
import {waitForMagewireIdle} from '../actions/WaitForMagewireIdle';
import {closePickupPointPicker, selectColissimoPickupRetrait} from '../actions/PickupPointPicker';
import {storeViewDefault, storeViewOnepage} from '../../../playwright.config';

const pickupPointRequiredMessage = 'Veuillez sélectionner un point de retrait avant de continuer.';

async function browsePickupPointsWithoutSelecting(page: Page) {
    await selectColissimoPickupRetrait(page);
    await closePickupPointPicker(page);
}

async function selectFlatRateShippingMethod(page: Page) {
    const flatRate = page.locator('label', { has: page.locator('input[value="flatrate_flatrate"]') });

    await flatRate.waitFor({ state: 'visible', timeout: 30000 });
    await page.waitForSelector('.loading-mask', { state: 'hidden', timeout: 30000 });
    await flatRate.click();
    await page.waitForSelector('.loading-mask', { state: 'hidden', timeout: 30000 });
    await waitForMagewireIdle(page);

    await expect(page.locator('input[name="shipping-method-option"]:checked')).toHaveValue('flatrate_flatrate');
}

test('Can go to payment page with another shipping method after browsing pickup points in default page view', async ({ page }) => {
    test.setTimeout(120000);

    await (new LoadCheckout(storeViewDefault)).execute(page);
    await browsePickupPointsWithoutSelecting(page);
    await selectFlatRateShippingMethod(page);

    await page.getByRole('button', { name: 'Proceed to Vérification &' }).click();
    await page.waitForSelector('.loading-mask', { state: 'hidden', timeout: 30000 });

    await expect(page.getByRole('group', { name: 'Mode de paiement' })).toBeVisible({ timeout: 30000 });
    await waitForMagewireIdle(page);

    expect(await page.getByText(pickupPointRequiredMessage).isVisible()).toBe(false);
})

test('Can place an order with another shipping method after browsing pickup points in one page view', async ({ page }) => {
    test.setTimeout(120000);

    await (new LoadCheckout(storeViewOnepage)).execute(page);
    await browsePickupPointsWithoutSelecting(page);
    await selectFlatRateShippingMethod(page);

    await page.getByRole('group', { name: 'Mode de paiement' }).locator('label').first().click();
    await page.waitForSelector('.loading-mask', { state: 'hidden', timeout: 30000 });
    await waitForMagewireIdle(page);
    const isPickupPointRequiredMessageShown = page.getByText(pickupPointRequiredMessage)
        .waitFor({ state: 'visible', timeout: 10000 })
        .then(() => true, () => false);
    await page.getByRole('button', { name: 'Passez la commande' }).click();

    expect(await isPickupPointRequiredMessageShown).toBe(false);
    await expect(page).toHaveURL(new RegExp(`checkout/onepage/success`), { timeout: 30000 });
})
