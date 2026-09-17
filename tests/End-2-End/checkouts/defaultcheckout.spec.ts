import {expect, test} from '@playwright/test';
import LoadCheckout from '../actions/LoadCheckout';
import {waitForMagewireIdle} from '../actions/WaitForMagewireIdle';
import {closePickupPointPicker, selectColissimoPickupRetrait} from '../actions/PickupPointPicker';
import {storeViewDefault} from '../../../playwright.config';

test('Can make an successful order/pay in default page view', async ({ page }) => {
    test.setTimeout(120000);

    await (new LoadCheckout(storeViewDefault)).execute(page);

    await selectColissimoPickupRetrait(page);
    await page.getByRole('button', { name: 'Sélectionnez ce point de' }).first().click();
    await page.waitForSelector('.loading-mask', { state: 'hidden', timeout: 30000 });
    await waitForMagewireIdle(page);

    await page.getByRole('button', { name: 'Proceed to Vérification &' }).click();
    await page.waitForSelector('.loading-mask', { state: 'hidden', timeout: 30000 });

    await page.getByRole('group', { name: 'Mode de paiement' }).locator('label').waitFor({ state: 'visible', timeout: 30000 });
    await page.getByRole('group', { name: 'Mode de paiement' }).locator('label').click();
    await waitForMagewireIdle(page);
    await page.getByRole('button', {name: 'Passez la commande' }).click();

    await expect(page).toHaveURL(new RegExp(`checkout/onepage/success`), { timeout: 30000 });
})

test('Cant go to payment page without selecting pickup point',  async ({ page }) => {
    test.setTimeout(120000);

    await (new LoadCheckout(storeViewDefault)).execute(page);

    await selectColissimoPickupRetrait(page);
    await closePickupPointPicker(page);

    await page.getByRole('button', { name: 'Proceed to Vérification &' }).click();
    await page.waitForSelector('.loading-mask', { state: 'hidden', timeout: 30000 });
    await expect(page.getByText('Veuillez sélectionner un point de retrait avant de continuer.')).toBeVisible({ timeout: 10000 });
})
