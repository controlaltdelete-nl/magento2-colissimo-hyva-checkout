#!/usr/bin/env bash
set -e
set -x

# Check if $HYVA_SSH_PRIVATE_KEY is set
if [ -z "$HYVA_SSH_PRIVATE_KEY" ]; then
    echo "Variable \$HYVA_SSH_PRIVATE_KEY is not set"
    exit 1
fi

eval `ssh-agent -s`
mkdir -p ~/.ssh/ && touch ~/.ssh/known_hosts
echo "$HYVA_SSH_PRIVATE_KEY" | ssh-add -
ssh-keyscan -t rsa gitlab.hyva.io >> ~/.ssh/known_hosts

composer config repositories.hyva-themes/magento2-theme-module git git@gitlab.hyva.io:hyva-themes/magento2-theme-module.git
composer config repositories.hyva-themes/magento2-reset-theme git git@gitlab.hyva.io:hyva-themes/magento2-reset-theme.git
composer config repositories.hyva-themes/magento2-email-module git git@gitlab.hyva.io:hyva-themes/magento2-email-module.git
composer config repositories.hyva-themes/magento2-default-theme git git@gitlab.hyva.io:hyva-themes/magento2-default-theme.git
composer config repositories.hyva-themes/magento2-default-theme-csp git git@gitlab.hyva.io:hyva-themes/magento2-default-theme-csp.git
composer config repositories.hyva-themes/magento2-compat-module-fallback git git@gitlab.hyva.io:hyva-themes/magento2-compat-module-fallback.git
composer config repositories.hyva-themes/magento2-order-cancellation-webapi git git@gitlab.hyva.io:hyva-themes/magento2-order-cancellation-webapi.git
composer config repositories.hyva-themes/hyva-checkout git git@gitlab.hyva.io:hyva-checkout/checkout.git
composer config repositories.hyva-themes/hyva-compat/magento2-mollie-theme-bundle git git@gitlab.hyva.io:hyva-themes/hyva-compat/magento2-mollie-theme-bundle.git
composer config repositories.hyva-themes/magento2-base-layout-reset git git@gitlab.hyva.io:hyva-themes/magento2-base-layout-reset.git

composer require hyva-themes/magento2-default-theme-csp hyva-themes/magento2-hyva-checkout

bin/magento setup:upgrade --keep-generated

# Enable CSP
magerun2 config:store:set system/default/csp/policies/storefront/scripts/inline 0
magerun2 config:store:set system/default/csp/policies/storefront/scripts/eval 0
magerun2 config:store:set system/default/csp/mode/storefront/report_only 0

bin/magento config:set general/region/display_all 0
bin/magento config:set hyva_themes_checkout/general/checkout default

bin/magento hyva:config:generate

npm --prefix vendor/hyva-themes/magento2-default-theme-csp/web/tailwind/ ci
npm --prefix vendor/hyva-themes/magento2-default-theme-csp/web/tailwind/ run build