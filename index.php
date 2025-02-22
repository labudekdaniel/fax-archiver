<?php
require 'vendor/autoload.php';

use App\Config;
use App\Logger;
use App\FaxProcessor;
use App\PdfGenerator;

$config = new Config('config.json');
$logger = new Logger($config);

$logger->log("Script started.");

$faxInputDir = $config->get('paths.fax_input_dir');
$customerDataFile = $config->get('paths.customer_data_file');
$outputPdfDir = $config->get('paths.output_pdf_dir');
$processedFaxesFile = $config->get('paths.processed_faxes_file');

$faxProcessor = new FaxProcessor($faxInputDir, $customerDataFile, $processedFaxesFile, $logger);
$pdfGenerator = new PdfGenerator($outputPdfDir, $config, $logger);

$customers = $faxProcessor->scanAndProcessFaxes();

foreach ($customers as $customer) {
    if (!empty($customer->faxes)) {
        $pdfGenerator->generateCustomerPdf($customer);
    }
}

$logger->log("Script completed.");
