import {expect, test} from '@playwright/test';
import LoadCheckout from '../actions/LoadCheckout';
import {waitForMagewireIdle} from '../actions/WaitForMagewireIdle';
import {storeViewDefault} from '../../../playwright.config';

test('The pickup point picker opens when Colissimo Pickup Retrait is selected without a pickup point', async ({ page }) => {
    test.setTimeout(120000);

    await (new LoadCheckout(storeViewDefault)).execute(page);
    await waitForMagewireIdle(page);

    await page.locator('label', { hasText: 'Colissimo Pickup Retrait' }).click();

    await expect(page.getByRole('dialog', { name: 'Point relais' })).toBeVisible({ timeout: 30000 });
    await expect(page.getByRole('button', { name: 'Sélectionnez ce point de' }).first()).toBeVisible({ timeout: 30000 });
})

test('The pickup point picker does not open again when a pickup point is already selected', async ({ page }) => {
    test.setTimeout(120000);

    await (new LoadCheckout(storeViewDefault)).execute(page);
    await waitForMagewireIdle(page);

    await page.locator('label', { hasText: 'Colissimo Pickup Retrait' }).click();
    await page.getByRole('button', { name: 'Sélectionnez ce point de' }).first().click();
    await waitForMagewireIdle(page);
    await expect(page.getByRole('dialog', { name: 'Point relais' })).toBeHidden();

    await page.locator('label', { has: page.locator('input[value="flatrate_flatrate"]') }).click();
    await waitForMagewireIdle(page);
    await page.locator('label', { hasText: 'Colissimo Pickup Retrait' }).click();
    await waitForMagewireIdle(page);

    await expect(page.getByRole('dialog', { name: 'Point relais' })).toBeHidden();
})
