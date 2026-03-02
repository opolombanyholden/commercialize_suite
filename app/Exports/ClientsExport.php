<?php

namespace App\Exports;

use App\Models\Client;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ClientsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected int $companyId;

    public function __construct(int $companyId)
    {
        $this->companyId = $companyId;
    }

    public function query()
    {
        return Client::query()
            ->where('company_id', $this->companyId)
            ->orderBy('name');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Type',
            'Nom',
            'Entreprise',
            'N° Fiscal',
            'Email',
            'Téléphone',
            'Mobile',
            'Adresse',
            'Ville',
            'Code postal',
            'Pays',
            'Total dépensé',
            'Nombre commandes',
            'Actif',
            'Créé le',
        ];
    }

    public function map($client): array
    {
        return [
            $client->id,
            $client->type === 'individual' ? 'Particulier' : 'Entreprise',
            $client->name,
            $client->company_name,
            $client->tax_id,
            $client->email,
            $client->phone,
            $client->mobile,
            $client->address,
            $client->city,
            $client->postal_code,
            $client->country,
            number_format($client->total_spent, 0, ',', ' '),
            $client->orders_count,
            $client->is_active ? 'Oui' : 'Non',
            $client->created_at->format('d/m/Y'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '004E89'],
                ],
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
            ],
        ];
    }
}
