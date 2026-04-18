import {expect} from "@playwright/test";

export default class LoadCheckout {
    checkout: string;

    constructor(checkout: string) {
        this.checkout = checkout;
    }

    async execute(page) {
        // Warmup request to avoid cold-start delays on first test
        await page.goto(`/${this.checkout}/`, { waitUntil: 'networkidle' });

        await page.locator('form[data-sku="24-MG04"]').locator('button[data-addto="cart"]').click();
        await page.waitForSelector('.loading-mask', { state: 'hidden', timeout: 30000 });

        await page.goto(`/${this.checkout}/checkout`, { waitUntil: 'networkidle' });

        await expect(page).toHaveURL(/\/checkout/);

        await page.getByRole('group', { name: 'Informations de contact' }).getByLabel('Adresse email').fill('piet@gmail.com');
        await page.getByRole('group', { name: 'Adresse de livraison' }).getByLabel('Prénom').fill('Piet');
        await page.getByRole('group', { name: 'Adresse de livraison' }).getByLabel('Nom', { exact: true }).fill('Gert');
        await page.getByRole('group', { name: 'Adresse de livraison' }).getByLabel('Adresse').fill('Pietenstraat 14');
        await page.getByRole('group', { name: 'Adresse de livraison' }).getByLabel('Pays').selectOption('FR');
        await page.getByRole('group', { name: 'Adresse de livraison' }).getByLabel('Code Postal').fill('75001');
        await page.getByRole('group', { name: 'Adresse de livraison' }).getByLabel('Ville').fill('Wipou');
        await page.getByRole('group', { name: 'Adresse de livraison' }).getByLabel('Numéro de téléphone').fill('08234328589');
    }
}
