import {expect, Page} from '@playwright/test';

/**
 * Wait until no magewire/post requests have fired for `quietMs` consecutive milliseconds.
 *
 * Selecting a pickup point triggers a cascade of magewire component updates (our RelayPicker
 * emits `shipping_address_saved`, which Hyvä's MethodList listens for and refreshes in turn).
 * The gaps between cascades are longer than Playwright's `networkidle` (500ms), so we track
 * the timestamp of the last magewire request and wait until it has been quiet long enough.
 */
export async function waitForMagewireIdle(page: Page, quietMs = 1500, timeout = 20000): Promise<void> {
    let lastMagewireAt = Date.now();
    const onMagewire = (event: {url: () => string}) => {
        if (event.url().includes('/magewire/post/')) {
            lastMagewireAt = Date.now();
        }
    };
    page.on('request', onMagewire);
    page.on('response', onMagewire);
    try {
        await expect.poll(() => Date.now() - lastMagewireAt, {timeout}).toBeGreaterThan(quietMs);
    } finally {
        page.off('request', onMagewire);
        page.off('response', onMagewire);
    }
}
