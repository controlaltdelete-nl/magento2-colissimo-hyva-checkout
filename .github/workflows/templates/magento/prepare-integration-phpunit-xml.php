<?php

declare(strict_types=1);

const SOURCE = 'dev/tests/integration/phpunit.xml.dist';
const TARGET = 'dev/tests/integration/phpunit.xml';

if (file_exists(TARGET)) {
    echo TARGET . ' already exists' . PHP_EOL;
    exit(0);
}

$document = new DOMDocument();
$document->preserveWhiteSpace = false;
$document->formatOutput = true;
$document->load(SOURCE);

$xpath = new DOMXPath($document);
$allureNodes = $xpath->query(
    '//listener[contains(@class, "Allure")]|//extension[contains(@class, "Allure")]|//bootstrap[contains(@class, "Allure")]'
);
foreach ($allureNodes as $allureNode) {
    $allureNode->parentNode->removeChild($allureNode);
}

foreach ($xpath->query('//listeners[not(*)]|//extensions[not(*)]') as $emptyNode) {
    $emptyNode->parentNode->removeChild($emptyNode);
}

$document->save(TARGET);

echo 'Created ' . TARGET . PHP_EOL;
