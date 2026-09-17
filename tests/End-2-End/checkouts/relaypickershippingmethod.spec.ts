import {expect, test} from '@playwright/test';
import LoadCheckout from '../actions/LoadCheckout';
import {waitForMagewireIdle} from '../actions/WaitForMagewireIdle';
import {selectColissimoPickupRetrait} from '../actions/PickupPointPicker';
import {storeViewOnepage} from '../../../playwright.config';

test.use({ ignoreHTTPSErrors: true });

test('Selecting a pickup point keeps Colissimo Pickup Retrait as the active shipping method', async ({ page }) => {
    test.setTimeout(120000);

    await (new LoadCheckout(storeViewOnepage)).execute(page);

    // Select the Colissimo Pickup Retrait shipping method, which opens the relay picker
    await selectColissimoPickupRetrait(page);

    // Sanity check: Colissimo PR is the selected method before picking a relay point
    await expect(page.locator('input[name="shipping-method-option"]:checked')).toHaveValue('colissimo_pr');

    await page.getByRole('button', { name: 'Sélectionnez ce point de' }).first().click();
    await page.waitForSelector('.loading-mask', { state: 'hidden', timeout: 30000 });
    await waitForMagewireIdle(page);

    // Bug: after selecting a pickup point, the shipping method flips to "Fixed" (flatrate).
    // Expected: Colissimo Pickup Retrait remains the active shipping method.
    await expect(page.locator('input[name="shipping-method-option"]:checked')).toHaveValue('colissimo_pr');
    await expect(page.locator('#shipping-method-option-pr')).toHaveClass(/active/);
});
