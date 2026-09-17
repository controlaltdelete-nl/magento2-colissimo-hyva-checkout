import {expect, test} from '@playwright/test';
import LoadCheckout from '../actions/LoadCheckout';
import {waitForMagewireIdle} from '../actions/WaitForMagewireIdle';
import {selectColissimoPickupRetrait} from '../actions/PickupPointPicker';
import {storeViewDefault} from '../../../playwright.config';

test('Google Maps is only loaded when the pickup point picker is opened', async ({ page }) => {
    test.setTimeout(120000);

    const googleMapsRequests: string[] = [];
    page.on('request', request => {
        if (request.url().includes('maps.googleapis.com')) {
            googleMapsRequests.push(request.url());
        }
    });

    await (new LoadCheckout(storeViewDefault)).execute(page);
    await page.locator('label', { has: page.locator('input[value="flatrate_flatrate"]') }).click();
    await waitForMagewireIdle(page);

    expect(googleMapsRequests).toHaveLength(0);

    await selectColissimoPickupRetrait(page);

    await expect.poll(() => googleMapsRequests.length, { timeout: 30000 }).toBeGreaterThan(0);
})
