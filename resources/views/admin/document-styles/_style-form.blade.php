<form action="{{ route('settings.documents.update', $type) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    @php
        $contentTypes = \App\Models\DocumentStyleBlock::CONTENT_TYPES;
        $headerBlocks = [
            'left'   => $style->getBlockFor('header', 'left'),
            'center' => $style->getBlockFor('header', 'center'),
            'right'  => $style->getBlockFor('header', 'right'),
        ];
        $footerBlocks = [
            'left'   => $style->getBlockFor('footer', 'left'),
            'center' => $style->getBlockFor('footer', 'center'),
            'right'  => $style->getBlockFor('footer', 'right'),
        ];
        $hWidths = $style->getHeaderWidths();
        $fWidths = $style->getFooterWidths();
        $posLabels = ['left' => 'Gauche', 'center' => 'Centre', 'right' => 'Droite'];
    @endphp

    <div class="row">
        <div class="col-lg-8">

            {{-- ============ EN-TETE DE PAGE ============ --}}
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="fas fa-heading me-2"></i>En-tete de page</h5>
                    <small class="text-muted">Repete sur chaque page</small>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Hauteur (cm)</label>
                            <input type="number" class="form-control" name="header_height_cm"
                                   value="{{ old('header_height_cm', $style->header_height_cm ?? 2.0) }}"
                                   min="1" max="6" step="0.5">
                        </div>
                    </div>

                    <p class="text-muted mb-3"><small>Configurez les 3 blocs de l'en-tete (Gauche, Centre, Droite). Chaque bloc peut recevoir un contenu predefini ou personnalise.</small></p>

                    <div class="row g-3">
                        @foreach(['left', 'center', 'right'] as $pos)
                        @php
                            $block = $headerBlocks[$pos];
                            $ct = old("header_blocks.{$pos}.content_type", $block->content_type ?? 'empty');
                            $chtml = old("header_blocks.{$pos}.custom_html", $block->custom_html ?? '');
                            $wp = old("header_blocks.{$pos}.width_percent", $block->width_percent ?? $hWidths[$pos]);
                        @endphp
                        <div class="col-md-4">
                            <div class="card border h-100">
                                <div class="card-header py-2 bg-light">
                                    <strong>{{ $posLabels[$pos] }}</strong>
                                </div>
                                <div class="card-body p-3">
                                    <div class="mb-2">
                                        <label class="form-label form-label-sm">Contenu</label>
                                        <select class="form-select form-select-sm block-content-type"
                                                name="header_blocks[{{ $pos }}][content_type]"
                                                data-target="header_{{ $type }}_{{ $pos }}_html">
                                            @foreach($contentTypes as $val => $label)
                                            <option value="{{ $val }}" {{ $ct === $val ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label form-label-sm">Largeur (%)</label>
                                        <input type="number" class="form-control form-control-sm block-width"
                                               name="header_blocks[{{ $pos }}][width_percent]"
                                               value="{{ $wp }}" min="0" max="100"
                                               data-section="header_{{ $type }}">
                                    </div>
                                    <div class="mb-0" id="header_{{ $type }}_{{ $pos }}_html_wrapper"
                                         style="{{ in_array($ct, ['custom_html', 'service_info']) ? '' : 'display:none;' }}">
                                        <label class="form-label form-label-sm">HTML personnalise</label>
                                        <textarea class="form-control form-control-sm" rows="3"
                                                  name="header_blocks[{{ $pos }}][custom_html]"
                                                  id="header_{{ $type }}_{{ $pos }}_html"
                                                  placeholder="<strong>Mon contenu</strong>">{{ $chtml }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="mt-2">
                        <small class="text-muted width-sum-indicator" id="header_{{ $type }}_sum">
                            Total largeurs : <span>{{ $hWidths['left'] + $hWidths['center'] + $hWidths['right'] }}</span>%
                        </small>
                    </div>
                </div>
            </div>

            {{-- ============ TITRE DU DOCUMENT ============ --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-font me-2"></i>Titre du document</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Position du titre</label>
                            <div class="btn-group w-100" role="group">
                                @foreach(['left' => 'Gauche', 'center' => 'Centre', 'right' => 'Droite'] as $val => $label)
                                <input type="radio" class="btn-check" name="title_position" id="title_{{ $type }}_{{ $val }}" value="{{ $val }}"
                                    {{ old('title_position', $style->title_position ?? 'right') === $val ? 'checked' : '' }}>
                                <label class="btn btn-outline-primary" for="title_{{ $type }}_{{ $val }}">{{ $label }}</label>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="primary_color_{{ $type }}" class="form-label">Couleur principale (accent)</label>
                            <input type="color" class="form-control form-control-color w-100"
                                   id="primary_color_{{ $type }}" name="primary_color"
                                   value="{{ old('primary_color', $style->primary_color) }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- ============ BLOC DESTINATAIRE ============ --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-user me-2"></i>Bloc Destinataire</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="client_box_bg_color_{{ $type }}" class="form-label">Couleur de fond</label>
                            <input type="color" class="form-control form-control-color w-100"
                                   id="client_box_bg_color_{{ $type }}" name="client_box_bg_color"
                                   value="{{ old('client_box_bg_color', $style->client_box_bg_color ?? '#e3f2fd') }}">
                        </div>
                        <div class="col-md-6">
                            <label for="client_box_border_color_{{ $type }}" class="form-label">Couleur de bordure</label>
                            <input type="color" class="form-control form-control-color w-100"
                                   id="client_box_border_color_{{ $type }}" name="client_box_border_color"
                                   value="{{ old('client_box_border_color', $style->client_box_border_color ?? ($style->primary_color ?? '#2196F3')) }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- ============ TABLEAU DE DONNEES ============ --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-table me-2"></i>Tableau de donnees</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="table_header_color_{{ $type }}" class="form-label">Couleur en-tete tableau</label>
                            <input type="color" class="form-control form-control-color w-100"
                                   id="table_header_color_{{ $type }}" name="table_header_color"
                                   value="{{ old('table_header_color', $style->table_header_color) }}">
                        </div>
                        <div class="col-md-4">
                            <label for="table_odd_row_color_{{ $type }}" class="form-label">Couleur lignes impaires</label>
                            <input type="color" class="form-control form-control-color w-100"
                                   id="table_odd_row_color_{{ $type }}" name="table_odd_row_color"
                                   value="{{ old('table_odd_row_color', $style->table_odd_row_color ?? '#FFFFFF') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="table_even_row_color_{{ $type }}" class="form-label">Couleur lignes paires</label>
                            <input type="color" class="form-control form-control-color w-100"
                                   id="table_even_row_color_{{ $type }}" name="table_even_row_color"
                                   value="{{ old('table_even_row_color', $style->table_even_row_color ?? '#FAFAFA') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="table_text_color_{{ $type }}" class="form-label">Couleur du texte</label>
                            <input type="color" class="form-control form-control-color w-100"
                                   id="table_text_color_{{ $type }}" name="table_text_color"
                                   value="{{ old('table_text_color', $style->table_text_color ?? '#333333') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="table_font_family_{{ $type }}" class="form-label">Police</label>
                            <select class="form-select" id="table_font_family_{{ $type }}" name="table_font_family">
                                @foreach($fonts as $value => $label)
                                <option value="{{ $value }}" {{ old('table_font_family', $style->table_font_family ?? 'DejaVu Sans') === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="table_font_style_{{ $type }}" class="form-label">Style de texte</label>
                            <select class="form-select" id="table_font_style_{{ $type }}" name="table_font_style">
                                <option value="normal" {{ old('table_font_style', $style->table_font_style ?? 'normal') === 'normal' ? 'selected' : '' }}>Normal</option>
                                <option value="bold" {{ old('table_font_style', $style->table_font_style ?? 'normal') === 'bold' ? 'selected' : '' }}>Gras</option>
                                <option value="italic" {{ old('table_font_style', $style->table_font_style ?? 'normal') === 'italic' ? 'selected' : '' }}>Italique</option>
                                <option value="bold_italic" {{ old('table_font_style', $style->table_font_style ?? 'normal') === 'bold_italic' ? 'selected' : '' }}>Gras italique</option>
                            </select>
                        </div>
                    </div>

                    {{-- Mini preview --}}
                    <div class="mt-4">
                        <label class="form-label text-muted">Apercu du tableau</label>
                        <div class="border rounded p-2" id="tablePreview_{{ $type }}">
                            <table class="table table-sm mb-0" style="font-size: 0.85rem;">
                                <thead>
                                    <tr id="previewHead_{{ $type }}" style="background-color: {{ $style->table_header_color }}; color: #fff;">
                                        <th>#</th><th>Description</th><th>Qte</th><th>Total</th>
                                    </tr>
                                </thead>
                                <tbody id="previewBody_{{ $type }}">
                                    <tr style="background-color: {{ $style->table_odd_row_color ?? '#FFFFFF' }}; color: {{ $style->table_text_color ?? '#333' }};">
                                        <td>1</td><td>Article exemple</td><td>2</td><td>50 000 FCFA</td>
                                    </tr>
                                    <tr style="background-color: {{ $style->table_even_row_color ?? '#FAFAFA' }}; color: {{ $style->table_text_color ?? '#333' }};">
                                        <td>2</td><td>Autre article</td><td>1</td><td>25 000 FCFA</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ============ CONDITIONS DE VENTE ============ --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-gavel me-2"></i>Conditions de vente</h5>
                </div>
                <div class="card-body">
                    <textarea class="form-control mb-3" name="sales_conditions" rows="4"
                              placeholder="Saisissez vos conditions de vente...">{{ old('sales_conditions', $style->sales_conditions) }}</textarea>
                    <div class="form-text mb-3">Optionnel. Si vide, les conditions de vente definies au niveau de l'entreprise seront utilisees.</div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="conditions_bg_color_{{ $type }}" class="form-label">Couleur de fond</label>
                            <input type="color" class="form-control form-control-color w-100"
                                   id="conditions_bg_color_{{ $type }}" name="conditions_bg_color"
                                   value="{{ old('conditions_bg_color', $style->conditions_bg_color ?? '#f5f5f5') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="conditions_border_color_{{ $type }}" class="form-label">Couleur de bordure</label>
                            <input type="color" class="form-control form-control-color w-100"
                                   id="conditions_border_color_{{ $type }}" name="conditions_border_color"
                                   value="{{ old('conditions_border_color', $style->conditions_border_color ?? '#dddddd') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="conditions_width_{{ $type }}" class="form-label">Largeur (%)</label>
                            <input type="number" class="form-control"
                                   id="conditions_width_{{ $type }}" name="conditions_width"
                                   value="{{ old('conditions_width', $style->conditions_width ?? 100) }}"
                                   min="30" max="100" step="5">
                        </div>
                    </div>
                </div>
            </div>

            {{-- ============ PIED DE PAGE ============ --}}
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="fas fa-shoe-prints me-2"></i>Pied de page</h5>
                    <small class="text-muted">Repete sur chaque page</small>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Hauteur (cm)</label>
                            <input type="number" class="form-control" name="footer_height_cm"
                                   value="{{ old('footer_height_cm', $style->footer_height_cm ?? 1.0) }}"
                                   min="0.5" max="4" step="0.5">
                        </div>
                    </div>

                    <p class="text-muted mb-3"><small>Configurez les 3 blocs du pied de page (Gauche, Centre, Droite).</small></p>

                    <div class="row g-3">
                        @foreach(['left', 'center', 'right'] as $pos)
                        @php
                            $block = $footerBlocks[$pos];
                            $ct = old("footer_blocks.{$pos}.content_type", $block->content_type ?? 'empty');
                            $chtml = old("footer_blocks.{$pos}.custom_html", $block->custom_html ?? '');
                            $wp = old("footer_blocks.{$pos}.width_percent", $block->width_percent ?? $fWidths[$pos]);
                        @endphp
                        <div class="col-md-4">
                            <div class="card border h-100">
                                <div class="card-header py-2 bg-light">
                                    <strong>{{ $posLabels[$pos] }}</strong>
                                </div>
                                <div class="card-body p-3">
                                    <div class="mb-2">
                                        <label class="form-label form-label-sm">Contenu</label>
                                        <select class="form-select form-select-sm block-content-type"
                                                name="footer_blocks[{{ $pos }}][content_type]"
                                                data-target="footer_{{ $type }}_{{ $pos }}_html">
                                            @foreach($contentTypes as $val => $label)
                                            <option value="{{ $val }}" {{ $ct === $val ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label form-label-sm">Largeur (%)</label>
                                        <input type="number" class="form-control form-control-sm block-width"
                                               name="footer_blocks[{{ $pos }}][width_percent]"
                                               value="{{ $wp }}" min="0" max="100"
                                               data-section="footer_{{ $type }}">
                                    </div>
                                    <div class="mb-0" id="footer_{{ $type }}_{{ $pos }}_html_wrapper"
                                         style="{{ in_array($ct, ['custom_html', 'service_info']) ? '' : 'display:none;' }}">
                                        <label class="form-label form-label-sm">HTML personnalise</label>
                                        <textarea class="form-control form-control-sm" rows="3"
                                                  name="footer_blocks[{{ $pos }}][custom_html]"
                                                  id="footer_{{ $type }}_{{ $pos }}_html"
                                                  placeholder="<strong>Mon contenu</strong>">{{ $chtml }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="mt-2">
                        <small class="text-muted width-sum-indicator" id="footer_{{ $type }}_sum">
                            Total largeurs : <span>{{ $fWidths['left'] + $fWidths['center'] + $fWidths['right'] }}</span>%
                        </small>
                    </div>
                </div>
            </div>

        </div>

        {{-- ============ SIDEBAR ============ --}}
        <div class="col-lg-4">
            {{-- Arriere-plan --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-fill-drip me-2"></i>Arriere-plan</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="background_color_{{ $type }}" class="form-label">Couleur de fond</label>
                        <input type="color" class="form-control form-control-color w-100"
                               id="background_color_{{ $type }}" name="background_color"
                               value="{{ old('background_color', $style->background_color ?? '#FFFFFF') }}">
                    </div>
                    <div class="mb-3">
                        <label for="background_image_{{ $type }}" class="form-label">Image de fond</label>
                        <input type="file" class="form-control" id="background_image_{{ $type }}" name="background_image" accept="image/*">
                        <div class="form-text">Max 2 Mo. Formats: JPG, PNG.</div>
                    </div>
                    @if($style->background_image)
                    <div class="mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <img src="{{ Storage::url($style->background_image) }}" alt="Background" class="rounded" style="max-height: 60px;">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="remove_bg_{{ $type }}" name="remove_background_image" value="1">
                                <label class="form-check-label text-danger" for="remove_bg_{{ $type }}">Supprimer</label>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Actions --}}
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-2"></i>Enregistrer
                        </button>
                        <a href="{{ route('settings.documents.preview', $type) }}" target="_blank" class="btn btn-outline-secondary">
                            <i class="fas fa-eye me-2"></i>Apercu PDF
                        </a>
                    </div>
                </div>
            </div>

            {{-- Reset --}}
            <div class="card border-danger">
                <div class="card-body text-center">
                    <p class="text-muted mb-2">Revenir aux styles par defaut</p>
                    <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#resetModal_{{ $type }}">
                        <i class="fas fa-undo me-1"></i>Reinitialiser
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

{{-- Reset Modal --}}
<div class="modal fade" id="resetModal_{{ $type }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmer la reinitialisation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Etes-vous sur de vouloir reinitialiser le style de ce document aux valeurs par defaut ? Cette action est irreversible.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form action="{{ route('settings.documents.reset', $type) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-undo me-1"></i>Reinitialiser
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
