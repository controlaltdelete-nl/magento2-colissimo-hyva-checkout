import {expect, Page} from '@playwright/test';
import {waitForMagewireIdle} from './WaitForMagewireIdle';

export async function selectColissimoPickupRetrait(page: Page): Promise<void> {
    const colissimoPickup = page.locator('label', { hasText: 'Colissimo Pickup Retrait' });

    await colissimoPickup.waitFor({ state: 'visible', timeout: 30000 });
    await page.waitForSelector('.loading-mask', { state: 'hidden', timeout: 30000 });
    await colissimoPickup.click();

    await expect(page.getByRole('dialog', { name: 'Point relais' })).toBeVisible({ timeout: 30000 });
    await page.getByRole('button', { name: 'Sélectionnez ce point de' }).first().waitFor({ state: 'visible', timeout: 30000 });
    await waitForMagewireIdle(page);
}

export async function closePickupPointPicker(page: Page): Promise<void> {
    await page.keyboard.press('Escape');
    await expect(page.getByRole('dialog', { name: 'Point relais' })).toBeHidden();
    await waitForMagewireIdle(page);
}
