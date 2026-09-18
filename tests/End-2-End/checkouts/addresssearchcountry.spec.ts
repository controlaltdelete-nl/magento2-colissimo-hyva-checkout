import {expect, test} from '@playwright/test';
import LoadCheckout from '../actions/LoadCheckout';
import {waitForMagewireIdle} from '../actions/WaitForMagewireIdle';
import {closePickupPointPicker, selectColissimoPickupRetrait} from '../actions/PickupPointPicker';
import {storeViewDefault} from '../../../playwright.config';

test('The address search is limited to the country of the shipping address', async ({ page }) => {
    test.setTimeout(120000);

    await (new LoadCheckout(storeViewDefault)).execute(page);
    await selectColissimoPickupRetrait(page);

    const addressSearch = page.locator('gmp-place-autocomplete');
    await expect(addressSearch).toHaveAttribute('included-region-codes', 'FR');
    await closePickupPointPicker(page);

    const shippingAddress = page.getByRole('group', { name: 'Adresse de livraison' });
    await shippingAddress.getByLabel('Pays').selectOption('NL');
    await shippingAddress.getByLabel('Code Postal').fill('1795AD');
    await shippingAddress.getByLabel('Ville').fill('De Cocksdorp');
    await shippingAddress.getByLabel('Ville').blur();
    await waitForMagewireIdle(page);

    await page.getByText('Choisissez le point de prise').click();

    await expect(addressSearch).toHaveAttribute('included-region-codes', 'NL');
})
