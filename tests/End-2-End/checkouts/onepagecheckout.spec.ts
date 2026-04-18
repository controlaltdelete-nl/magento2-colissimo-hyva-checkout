import {expect, test} from '@playwright/test';
import LoadCheckout from '../actions/LoadCheckout';
import {waitForMagewireIdle} from '../actions/WaitForMagewireIdle';
import {storeViewOnepage} from '../../../playwright.config';

test('Can make an successful order/pay in one page view', async ({ page }) => {
    test.setTimeout(120000);

    await (new LoadCheckout(storeViewOnepage)).execute(page);

    // Wait for shipping methods to be fully rendered instead of a fixed timeout
    await page.locator('label').filter({ hasText: 'Colissimo Pickup Retrait' }).waitFor({ state: 'visible', timeout: 30000 });
    await page.waitForSelector('.loading-mask', { state: 'hidden', timeout: 30000 });
    await page.locator('label').filter({ hasText: 'Colissimo Pickup Retrait' }).click();
    await page.getByText('Choisissez le point de prise').click();

    await page.waitForSelector('.loading-mask', { state: 'hidden', timeout: 30000 });
    await page.getByRole('button', { name: 'Sélectionnez ce point de' }).first().click();
    await page.waitForSelector('.loading-mask', { state: 'hidden', timeout: 30000 });
    await waitForMagewireIdle(page);

    await page.locator('label').filter({ hasText: 'Colissimo Pickup Retrait' }).click();
    await page.waitForSelector('.loading-mask', { state: 'hidden', timeout: 30000 });

    await page.getByRole('group', { name: 'Mode de paiement' }).locator('label').click();
    await page.getByRole('button', {name: 'Passez la commande' }).click();

    await expect(page).toHaveURL(new RegExp(`checkout/onepage/success`), { timeout: 30000 });
})

test('Cant go to payment without selecting pickup point',  async ({ page }) => {
    test.setTimeout(120000);

    await (new LoadCheckout(storeViewOnepage)).execute(page);

    // Wait for shipping methods to be fully rendered instead of a fixed timeout
    await page.locator('label', { hasText: 'Colissimo Pickup Retrait' }).waitFor({ state: 'visible', timeout: 30000 });
    await page.locator('label', { hasText: 'Colissimo Pickup Retrait' }).click();
    await page.waitForSelector('.loading-mask', { state: 'hidden', timeout: 30000 });
    await page.locator('label', { hasText: 'Check / Money order' }).click();
    await page.waitForSelector('.loading-mask', { state: 'hidden', timeout: 30000 });

    await page.getByRole('button', {name: 'Passez la commande' }).click();

    await expect(page.getByText('Veuillez sélectionner un point de retrait avant de continuer.')).toBeVisible({ timeout: 10000 });
})
