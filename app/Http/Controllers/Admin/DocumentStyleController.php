<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentStyle;
use App\Models\DocumentStyleBlock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DocumentStyleController extends Controller
{
    protected array $documentTypes = [
        'quote' => 'Devis',
        'invoice' => 'Factures',
        'delivery_note' => 'Bons de livraison',
        'payment_receipt' => 'Reçus de paiement',
    ];

    public function index(Request $request)
    {
        $companyId = $request->user()->company_id;

        $styles = [];
        foreach (array_keys($this->documentTypes) as $type) {
            $styles[$type] = DocumentStyle::forDocument($companyId, $type);
        }

        return view('admin.document-styles.index', [
            'styles' => $styles,
            'documentTypes' => $this->documentTypes,
            'fonts' => DocumentStyle::availableFonts(),
            'activeTab' => $request->get('tab', 'invoice'),
        ]);
    }

    public function update(Request $request, string $documentType)
    {
        if (!array_key_exists($documentType, $this->documentTypes)) {
            abort(404);
        }

        $validated = $request->validate([
            'title_position' => 'required|in:left,center,right',
            'primary_color' => 'nullable|string|max:7',
            'header_height_cm' => 'nullable|numeric|min:1|max:6',
            'footer_height_cm' => 'nullable|numeric|min:0.5|max:4',
            'sales_conditions' => 'nullable|string|max:5000',
            'background_color' => 'nullable|string|max:7',
            'table_header_color' => 'nullable|string|max:7',
            'table_odd_row_color' => 'nullable|string|max:7',
            'table_even_row_color' => 'nullable|string|max:7',
            'table_text_color' => 'nullable|string|max:7',
            'table_font_family' => 'required|string|max:50',
            'table_font_style' => 'required|in:normal,bold,italic,bold_italic',
            'client_box_bg_color' => 'nullable|string|max:7',
            'client_box_border_color' => 'nullable|string|max:7',
            'conditions_bg_color' => 'nullable|string|max:7',
            'conditions_border_color' => 'nullable|string|max:7',
            'conditions_width' => 'nullable|integer|min:30|max:100',
            'background_image' => 'nullable|image|max:2048',
            'remove_background_image' => 'nullable|boolean',
            // Block fields
            'header_blocks' => 'required|array',
            'header_blocks.*.content_type' => 'required|string|in:' . implode(',', array_keys(DocumentStyleBlock::CONTENT_TYPES)),
            'header_blocks.*.width_percent' => 'required|integer|min:0|max:100',
            'header_blocks.*.custom_html' => 'nullable|string|max:5000',
            'footer_blocks' => 'required|array',
            'footer_blocks.*.content_type' => 'required|string|in:' . implode(',', array_keys(DocumentStyleBlock::CONTENT_TYPES)),
            'footer_blocks.*.width_percent' => 'required|integer|min:0|max:100',
            'footer_blocks.*.custom_html' => 'nullable|string|max:5000',
        ]);

        $companyId = $request->user()->company_id;

        DB::transaction(function () use ($request, $validated, $companyId, $documentType) {
            // Get or create style
            $style = DocumentStyle::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->where('document_type', $documentType)
                ->first();

            if (!$style) {
                $style = new DocumentStyle();
                $style->company_id = $companyId;
                $style->document_type = $documentType;
            }

            // Handle background image
            if ($request->hasFile('background_image')) {
                if ($style->background_image) {
                    Storage::disk('public')->delete($style->background_image);
                }
                $validated['background_image'] = $request->file('background_image')
                    ->store('document-styles/backgrounds/' . $companyId, 'public');
            } elseif ($request->boolean('remove_background_image')) {
                if ($style->background_image) {
                    Storage::disk('public')->delete($style->background_image);
                }
                $validated['background_image'] = null;
            }

            // Extract block data before fill
            $headerBlocks = $validated['header_blocks'];
            $footerBlocks = $validated['footer_blocks'];
            unset($validated['header_blocks'], $validated['footer_blocks'], $validated['remove_background_image']);

            // Set widths from blocks
            $validated['header_left_width'] = $headerBlocks['left']['width_percent'] ?? 33;
            $validated['header_center_width'] = $headerBlocks['center']['width_percent'] ?? 34;
            $validated['header_right_width'] = $headerBlocks['right']['width_percent'] ?? 33;
            $validated['footer_left_width'] = $footerBlocks['left']['width_percent'] ?? 33;
            $validated['footer_center_width'] = $footerBlocks['center']['width_percent'] ?? 34;
            $validated['footer_right_width'] = $footerBlocks['right']['width_percent'] ?? 33;
            $validated['uses_block_system'] = true;

            $style->fill($validated);
            $style->save();

            // Delete old blocks and create new ones
            $style->blocks()->delete();

            foreach (['header', 'footer'] as $section) {
                $blocks = ($section === 'header') ? $headerBlocks : $footerBlocks;
                $order = 0;
                foreach (['left', 'center', 'right'] as $position) {
                    if (isset($blocks[$position])) {
                        $style->blocks()->create([
                            'section' => $section,
                            'position' => $position,
                            'width_percent' => $blocks[$position]['width_percent'] ?? 33,
                            'content_type' => $blocks[$position]['content_type'] ?? 'empty',
                            'custom_html' => $blocks[$position]['custom_html'] ?? null,
                            'sort_order' => $order++,
                        ]);
                    }
                }
            }
        });

        return redirect()
            ->route('settings.documents.index', ['tab' => $documentType])
            ->with('success', 'Style du document "' . $this->documentTypes[$documentType] . '" mis à jour avec succès.');
    }

    public function preview(Request $request, string $documentType)
    {
        if (!array_key_exists($documentType, $this->documentTypes)) {
            abort(404);
        }

        $companyId = $request->user()->company_id;
        $style = DocumentStyle::forDocument($companyId, $documentType);
        $company = $request->user()->company;

        return view('admin.document-styles.preview', [
            'style' => $style,
            'company' => $company,
            'documentType' => $documentType,
            'typeName' => $this->documentTypes[$documentType],
        ]);
    }

    public function reset(Request $request, string $documentType)
    {
        if (!array_key_exists($documentType, $this->documentTypes)) {
            abort(404);
        }

        $companyId = $request->user()->company_id;

        $style = DocumentStyle::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('document_type', $documentType)
            ->first();

        if ($style) {
            if ($style->background_image) {
                Storage::disk('public')->delete($style->background_image);
            }
            $style->delete(); // cascade deletes blocks
        }

        return redirect()
            ->route('settings.documents.index', ['tab' => $documentType])
            ->with('success', 'Style réinitialisé aux valeurs par défaut.');
    }
}
