import {expect, Page, test} from '@playwright/test';
import LoadCheckout from '../actions/LoadCheckout';
import {waitForMagewireIdle} from '../actions/WaitForMagewireIdle';
import {storeViewDefault} from '../../../playwright.config';

async function openPickupPoints(page: Page) {
    await page.getByText('Choisissez le point de prise').click();
    await page.getByRole('button', { name: 'Sélectionnez ce point de' }).first().waitFor({ state: 'visible', timeout: 30000 });
    await waitForMagewireIdle(page);
}

test('Pickup points are fetched again after the shipping address changes', async ({ page }) => {
    test.setTimeout(120000);

    await (new LoadCheckout(storeViewDefault)).execute(page);
    await waitForMagewireIdle(page);

    await page.locator('label', { hasText: 'Colissimo Pickup Retrait' }).waitFor({ state: 'visible', timeout: 30000 });
    await page.locator('label', { hasText: 'Colissimo Pickup Retrait' }).click();
    await waitForMagewireIdle(page);

    await openPickupPoints(page);
    await expect(page.locator('.pickup-point-item').first()).toContainText('75001');
    await page.keyboard.press('Escape');

    const shippingAddress = page.getByRole('group', { name: 'Adresse de livraison' });
    await shippingAddress.getByLabel('Code Postal').fill('30000');
    await shippingAddress.getByLabel('Ville').fill('Nîmes');
    await shippingAddress.getByLabel('Ville').blur();
    await waitForMagewireIdle(page);

    await openPickupPoints(page);
    await expect(page.locator('.pickup-point-item').first()).toContainText('30000');
})
