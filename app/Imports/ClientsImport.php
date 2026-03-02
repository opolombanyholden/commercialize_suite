<?php

namespace App\Imports;

use App\Models\Client;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;

class ClientsImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError
{
    use SkipsErrors;

    protected int $companyId;

    public function __construct(int $companyId)
    {
        $this->companyId = $companyId;
    }

    public function model(array $row)
    {
        // Ignorer les lignes vides
        if (empty($row['nom']) && empty($row['name'])) {
            return null;
        }

        $name = $row['nom'] ?? $row['name'] ?? '';
        $email = $row['email'] ?? null;

        // Vérifier si le client existe déjà (par email)
        if ($email) {
            $existing = Client::where('company_id', $this->companyId)
                ->where('email', $email)
                ->first();

            if ($existing) {
                // Mettre à jour le client existant
                $existing->update($this->mapRowToData($row));
                return null;
            }
        }

        return new Client(array_merge(
            ['company_id' => $this->companyId],
            $this->mapRowToData($row)
        ));
    }

    protected function mapRowToData(array $row): array
    {
        $type = $row['type'] ?? $row['Type'] ?? 'individual';
        if (in_array(strtolower($type), ['entreprise', 'business', 'pro', 'professionnel'])) {
            $type = 'business';
        } else {
            $type = 'individual';
        }

        return [
            'type' => $type,
            'name' => $row['nom'] ?? $row['name'] ?? '',
            'company_name' => $row['entreprise'] ?? $row['company_name'] ?? $row['societe'] ?? null,
            'tax_id' => $row['n_fiscal'] ?? $row['tax_id'] ?? $row['nif'] ?? null,
            'email' => $row['email'] ?? null,
            'phone' => $row['telephone'] ?? $row['phone'] ?? $row['tel'] ?? null,
            'mobile' => $row['mobile'] ?? $row['portable'] ?? null,
            'address' => $row['adresse'] ?? $row['address'] ?? null,
            'city' => $row['ville'] ?? $row['city'] ?? null,
            'postal_code' => $row['code_postal'] ?? $row['postal_code'] ?? $row['cp'] ?? null,
            'country' => $row['pays'] ?? $row['country'] ?? 'GA',
            'is_active' => true,
        ];
    }

    public function rules(): array
    {
        return [
            'nom' => ['nullable', 'string', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
        ];
    }
}
