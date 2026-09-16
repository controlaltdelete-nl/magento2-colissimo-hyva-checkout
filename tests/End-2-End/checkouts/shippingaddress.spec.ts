import {expect, test} from '@playwright/test';
import LoadCheckout from '../actions/LoadCheckout';
import {waitForMagewireIdle} from '../actions/WaitForMagewireIdle';
import {storeViewDefault} from '../../../playwright.config';

test('Selecting a pickup point keeps the customer shipping address in the checkout', async ({ page }) => {
    test.setTimeout(120000);

    await (new LoadCheckout(storeViewDefault)).execute(page);
    await waitForMagewireIdle(page);

    await page.locator('label', { hasText: 'Colissimo Pickup Retrait' }).waitFor({ state: 'visible', timeout: 30000 });
    await page.locator('label', { hasText: 'Colissimo Pickup Retrait' }).click();
    await waitForMagewireIdle(page);

    await page.getByText('Choisissez le point de prise').click();
    await page.getByRole('button', { name: 'Sélectionnez ce point de' }).first().click();
    await waitForMagewireIdle(page);

    await page.locator('label', { has: page.locator('input[value="flatrate_flatrate"]') }).click();
    await waitForMagewireIdle(page);

    await page.reload({ waitUntil: 'networkidle' });

    const shippingAddress = page.getByRole('group', { name: 'Adresse de livraison' });
    await expect(shippingAddress.getByLabel('Adresse', { exact: true })).toHaveValue('Pietenstraat 14', { timeout: 30000 });
    await expect(shippingAddress.getByLabel('Code Postal')).toHaveValue('75001');
    await expect(shippingAddress.getByLabel('Ville')).toHaveValue('Wipou');
})
