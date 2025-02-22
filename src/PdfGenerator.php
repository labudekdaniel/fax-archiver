<?php

namespace App;

use setasign\Fpdi\Fpdi;

class PdfGenerator {
    private string $outputDir;
    private Config $config;
    private Logger $logger;

    public function __construct(string $outputDir, Config $config, Logger $logger) {
        $this->outputDir = $outputDir;
        $this->config = $config;
        $this->logger = $logger;

        if (!is_dir($this->outputDir)) {
            mkdir($this->outputDir, 0777, true);
        }
    }

    public function generateCustomerPdf(Customer $customer): void {
        $filename = !empty($customer->name) ? str_replace(' ', '_', $customer->name) : $customer->id;
        $pdfFilename = "{$this->outputDir}/{$filename}.pdf";
        $pdf = new Fpdi();
        $pdf->SetAutoPageBreak(true, $this->config->get('pdf_settings.page_margin', 10));

        $font = $this->config->get('pdf_settings.default_font', 'Arial');
        $fontSize = $this->config->get('pdf_settings.font_size', 12);

        $pdf->AddPage();
        $pdf->SetFont($font, 'B', 16);
        $pdf->Cell(200, 10, 'Data', 0, 1, 'C');
        $pdf->Ln(10);

        $pdf->SetFont($font, '', $fontSize);
        $pdf->Cell(200, 10, 'Name: ' . $customer->name, 0, 1);
        $pdf->Cell(200, 10, 'Date of birth: ' . $customer->dateOfBirth, 0, 1);
        $pdf->Cell(200, 10, 'Number of faxes: ' . count($customer->faxes), 0, 1);
        $pdf->Ln(10);

        usort($customer->faxes, fn($a, $b) => strcmp($a->timestamp, $b->timestamp));

        foreach ($customer->faxes as $fax) {
            $pdf->AddPage();
            $formattedTime = \DateTime::createFromFormat('YmdHi', $fax->timestamp)->format(
                $this->config->get('locale.date_format')
            );
            $pdf->Cell(200, 10, "receipt date: $formattedTime", 0, 1, 'C');
            $pdf->Ln(10);

            $pages = array_filter(scandir($fax->path), fn($f) => preg_match('/^\\d+\\.png$/', $f));
            sort($pages, SORT_NATURAL);

            foreach ($pages as $page) {
                $pdf->AddPage();
                $pdf->Image("{$fax->path}/$page", 10, 20, 190);
            }
        }

        $pdf->Output('F', $pdfFilename);
        $this->logger->log("Generated PDF for customer: {$customer->name} (ID: {$customer->id}) → Saved as {$pdfFilename}");
    }
}
