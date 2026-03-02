<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

class PdfService
{
    /**
     * Options par défaut pour les PDF
     */
    protected array $defaultOptions = [
        'paper' => 'a4',
        'orientation' => 'portrait',
        'dpi' => 150,
        'defaultFont' => 'DejaVu Sans',
    ];

    /**
     * Générer un PDF à partir d'une vue Blade
     *
     * @param string $view Nom de la vue
     * @param array $data Données pour la vue
     * @param array $options Options de génération
     * @return \Barryvdh\DomPDF\PDF
     */
    public function generate(string $view, array $data = [], array $options = [])
    {
        $options = array_merge($this->defaultOptions, $options);

        $pdf = Pdf::loadView($view, $data)
            ->setPaper($options['paper'], $options['orientation']);

        return $pdf;
    }

    /**
     * Générer et télécharger un PDF
     *
     * @param string $view Nom de la vue
     * @param array $data Données pour la vue
     * @param string $filename Nom du fichier
     * @param array $options Options de génération
     * @return \Illuminate\Http\Response
     */
    public function download(string $view, array $data, string $filename, array $options = [])
    {
        $pdf = $this->generate($view, $data, $options);

        return $pdf->download($filename);
    }

    /**
     * Générer et afficher un PDF dans le navigateur
     *
     * @param string $view Nom de la vue
     * @param array $data Données pour la vue
     * @param array $options Options de génération
     * @return \Illuminate\Http\Response
     */
    public function stream(string $view, array $data, array $options = [])
    {
        $pdf = $this->generate($view, $data, $options);

        return $pdf->stream();
    }

    /**
     * Générer et sauvegarder un PDF sur le disque
     *
     * @param string $view Nom de la vue
     * @param array $data Données pour la vue
     * @param string $path Chemin de sauvegarde
     * @param array $options Options de génération
     * @return string Chemin du fichier
     */
    public function save(string $view, array $data, string $path, array $options = [])
    {
        $pdf = $this->generate($view, $data, $options);

        // S'assurer que le répertoire existe
        $directory = dirname($path);
        if (!Storage::exists($directory)) {
            Storage::makeDirectory($directory);
        }

        Storage::put($path, $pdf->output());

        return $path;
    }

    /**
     * Générer un PDF de facture
     */
    public function generateInvoice($invoice, array $options = []): string
    {
        $data = [
            'invoice' => $invoice,
            'company' => $invoice->company,
            'items' => $invoice->items,
            'taxes' => $invoice->taxes,
        ];

        $path = "invoices/{$invoice->company_id}/{$invoice->invoice_number}.pdf";

        $this->save('pdf.invoice', $data, $path, $options);

        // Mettre à jour le chemin dans le modèle
        $invoice->update([
            'pdf_path' => $path,
            'pdf_generated_at' => now(),
        ]);

        return $path;
    }

    /**
     * Générer un PDF de devis
     */
    public function generateQuote($quote, array $options = []): string
    {
        $data = [
            'quote' => $quote,
            'company' => $quote->company,
            'items' => $quote->items,
            'taxes' => $quote->taxes,
        ];

        $path = "quotes/{$quote->company_id}/{$quote->quote_number}.pdf";

        $this->save('pdf.quote', $data, $path, $options);

        // Mettre à jour le chemin dans le modèle
        $quote->update([
            'pdf_path' => $path,
            'pdf_generated_at' => now(),
        ]);

        return $path;
    }

    /**
     * Générer un reçu de paiement
     */
    public function generatePaymentReceipt($payment, array $options = []): string
    {
        $data = [
            'payment' => $payment,
            'invoice' => $payment->invoice,
            'company' => $payment->company,
        ];

        $path = "payments/{$payment->company_id}/{$payment->payment_number}.pdf";

        $this->save('pdf.payment-receipt', $data, $path, $options);

        return $path;
    }

    /**
     * Obtenir l'URL d'un PDF
     */
    public function getUrl(string $path): ?string
    {
        if (Storage::exists($path)) {
            return Storage::url($path);
        }

        return null;
    }

    /**
     * Supprimer un PDF
     */
    public function delete(string $path): bool
    {
        return Storage::delete($path);
    }
}
