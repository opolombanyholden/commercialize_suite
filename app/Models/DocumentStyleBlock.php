<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentStyleBlock extends Model
{
    protected $fillable = [
        'document_style_id',
        'section',
        'position',
        'width_percent',
        'content_type',
        'custom_html',
        'sort_order',
    ];

    public const CONTENT_TYPES = [
        'empty' => 'Vide',
        'logo' => 'Logo',
        'company_name' => 'Denomination',
        'address_contact' => 'Adresse & Contact',
        'legal_info' => 'Informations juridiques',
        'service_info' => 'Informations services',
        'custom_html' => 'Contenu personnalise',
    ];

    public function style(): BelongsTo
    {
        return $this->belongsTo(DocumentStyle::class, 'document_style_id');
    }

    /**
     * Render block content as HTML for PDF
     */
    public function render(Company $company): string
    {
        return match ($this->content_type) {
            'logo' => $this->renderLogo($company),
            'company_name' => $this->renderCompanyName($company),
            'address_contact' => $this->renderAddressContact($company),
            'legal_info' => $this->renderLegalInfo($company),
            'service_info' => $this->custom_html ?: '',
            'custom_html' => $this->custom_html ?: '',
            default => '',
        };
    }

    protected function renderLogo(Company $company): string
    {
        if (!$company->logo_path) {
            return '';
        }
        return '<img src="' . public_path('storage/' . $company->logo_path) . '" style="max-width:150px; max-height:60px;">';
    }

    protected function renderCompanyName(Company $company): string
    {
        $html = '<strong style="font-size:12pt;">' . e($company->name) . '</strong>';
        if ($company->legal_name) {
            $html .= '<br><span style="font-size:9pt; color:#666;">' . e($company->legal_name) . '</span>';
        }
        return $html;
    }

    protected function renderAddressContact(Company $company): string
    {
        $parts = [];
        if ($company->address) $parts[] = e($company->address);
        $cityLine = trim(($company->postal_code ?? '') . ' ' . ($company->city ?? ''));
        if ($cityLine) $parts[] = e($cityLine);
        if ($company->phone) $parts[] = 'Tel: ' . e($company->phone);
        if ($company->email) $parts[] = e($company->email);
        return '<span style="font-size:9pt; color:#666;">' . implode('<br>', $parts) . '</span>';
    }

    protected function renderLegalInfo(Company $company): string
    {
        $parts = [];
        if ($company->tax_id) $parts[] = 'N Fiscal: ' . e($company->tax_id);
        if ($company->registration_number) $parts[] = 'RC: ' . e($company->registration_number);
        if ($company->bank_name) $parts[] = 'Banque: ' . e($company->bank_name);
        if ($company->bank_account) $parts[] = 'Compte: ' . e($company->bank_account);
        if ($company->iban) $parts[] = 'IBAN: ' . e($company->iban);
        return '<span style="font-size:8pt; color:#666;">' . implode('<br>', $parts) . '</span>';
    }
}
