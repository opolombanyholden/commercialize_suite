<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentStyle extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'document_type',
        'header_content',
        'logo_position',
        'company_info_position',
        'title_position',
        'footer_content',
        'header_left_width',
        'header_center_width',
        'header_right_width',
        'footer_left_width',
        'footer_center_width',
        'footer_right_width',
        'header_height_cm',
        'footer_height_cm',
        'sales_conditions',
        'background_color',
        'background_image',
        'primary_color',
        'table_header_color',
        'table_odd_row_color',
        'table_even_row_color',
        'table_text_color',
        'table_font_family',
        'table_font_style',
        'client_box_bg_color',
        'client_box_border_color',
        'conditions_bg_color',
        'conditions_border_color',
        'conditions_width',
        'is_active',
        'uses_block_system',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'uses_block_system' => 'boolean',
        'header_height_cm' => 'decimal:1',
        'footer_height_cm' => 'decimal:1',
    ];

    public const DEFAULTS = [
        'quote' => [
            'primary_color' => '#2196F3',
            'table_header_color' => '#1976D2',
        ],
        'invoice' => [
            'primary_color' => '#FF6B35',
            'table_header_color' => '#004E89',
        ],
        'delivery_note' => [
            'primary_color' => '#4CAF50',
            'table_header_color' => '#388E3C',
        ],
        'payment_receipt' => [
            'primary_color' => '#9333EA',
            'table_header_color' => '#7E22CE',
        ],
    ];

    // ── Relationships ──

    public function blocks(): HasMany
    {
        return $this->hasMany(DocumentStyleBlock::class)->orderBy('sort_order');
    }

    // ── Block helpers ──

    public function getBlockFor(string $section, string $position): ?DocumentStyleBlock
    {
        return $this->blocks->first(function ($b) use ($section, $position) {
            return $b->section === $section && $b->position === $position;
        });
    }

    public function getHeaderWidths(): array
    {
        return [
            'left' => $this->header_left_width ?? 33,
            'center' => $this->header_center_width ?? 34,
            'right' => $this->header_right_width ?? 33,
        ];
    }

    public function getFooterWidths(): array
    {
        return [
            'left' => $this->footer_left_width ?? 33,
            'center' => $this->footer_center_width ?? 34,
            'right' => $this->footer_right_width ?? 33,
        ];
    }

    public function getEffectiveSalesConditions(): ?string
    {
        if ($this->sales_conditions) {
            return $this->sales_conditions;
        }
        return $this->company?->sales_conditions;
    }

    // ── Factory ──

    public static function forDocument(int $companyId, string $type): self
    {
        $style = static::withoutGlobalScopes()
            ->with('blocks')
            ->where('company_id', $companyId)
            ->where('document_type', $type)
            ->where('is_active', true)
            ->first();

        if ($style) {
            return $style;
        }

        $defaults = self::DEFAULTS[$type] ?? self::DEFAULTS['invoice'];

        return new self(array_merge([
            'company_id' => $companyId,
            'document_type' => $type,
            'logo_position' => 'left',
            'company_info_position' => 'left',
            'title_position' => 'right',
            'header_left_width' => 33,
            'header_center_width' => 34,
            'header_right_width' => 33,
            'footer_left_width' => 33,
            'footer_center_width' => 34,
            'footer_right_width' => 33,
            'header_height_cm' => 3.0,
            'footer_height_cm' => 2.5,
            'table_odd_row_color' => '#FFFFFF',
            'table_even_row_color' => '#FAFAFA',
            'table_text_color' => '#333333',
            'table_font_family' => 'DejaVu Sans',
            'table_font_style' => 'normal',
            'uses_block_system' => false,
        ], $defaults));
    }

    /**
     * Create default blocks for this style (header: logo left, address right; footer: company name left)
     */
    public function createDefaultBlocks(): void
    {
        $defaults = [
            ['section' => 'header', 'position' => 'left', 'width_percent' => 30, 'content_type' => 'logo'],
            ['section' => 'header', 'position' => 'center', 'width_percent' => 40, 'content_type' => 'company_name'],
            ['section' => 'header', 'position' => 'right', 'width_percent' => 30, 'content_type' => 'address_contact'],
            ['section' => 'footer', 'position' => 'left', 'width_percent' => 50, 'content_type' => 'company_name'],
            ['section' => 'footer', 'position' => 'center', 'width_percent' => 25, 'content_type' => 'legal_info'],
            ['section' => 'footer', 'position' => 'right', 'width_percent' => 25, 'content_type' => 'empty'],
        ];

        foreach ($defaults as $block) {
            $this->blocks()->create($block);
        }
    }

    // ── Accessors ──

    public function getPrimaryColorAttribute($value): string
    {
        return $value ?: (self::DEFAULTS[$this->document_type]['primary_color'] ?? '#FF6B35');
    }

    public function getTableHeaderColorAttribute($value): string
    {
        return $value ?: (self::DEFAULTS[$this->document_type]['table_header_color'] ?? '#004E89');
    }

    public function getFontWeightAttribute(): string
    {
        return in_array($this->table_font_style, ['bold', 'bold_italic']) ? 'bold' : 'normal';
    }

    public function getFontStyleCssAttribute(): string
    {
        return in_array($this->table_font_style, ['italic', 'bold_italic']) ? 'italic' : 'normal';
    }

    public static function availableFonts(): array
    {
        return [
            'DejaVu Sans' => 'DejaVu Sans',
            'DejaVu Serif' => 'DejaVu Serif',
            'DejaVu Sans Mono' => 'DejaVu Sans Mono',
            'Courier' => 'Courier',
            'Times New Roman' => 'Times New Roman',
        ];
    }
}
