import {expect, test} from '@playwright/test';
import LoadCheckout from '../actions/LoadCheckout';
import {waitForMagewireIdle} from '../actions/WaitForMagewireIdle';
import {selectColissimoPickupRetrait} from '../actions/PickupPointPicker';
import {storeViewOnepage} from '../../../playwright.config';

test('The selected pickup point is cleared after the order is placed', async ({ page }) => {
    test.setTimeout(180000);

    await (new LoadCheckout(storeViewOnepage)).execute(page);
    await selectColissimoPickupRetrait(page);
    await page.getByRole('button', { name: 'Sélectionnez ce point de' }).first().click();
    await waitForMagewireIdle(page);

    await page.getByRole('group', { name: 'Mode de paiement' }).locator('label').click();
    await waitForMagewireIdle(page);
    await page.getByRole('button', { name: 'Passez la commande' }).click();
    await expect(page).toHaveURL(new RegExp(`checkout/onepage/success`), { timeout: 30000 });

    await (new LoadCheckout(storeViewOnepage)).execute(page);
    await waitForMagewireIdle(page);
    await selectColissimoPickupRetrait(page);

    await expect(page.getByText('Point de ramassage sélectionné')).toHaveCount(0);
})
