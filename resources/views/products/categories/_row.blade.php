@php $indent = $level * 1.5; @endphp
<tr>
    <td>
        <div class="d-flex align-items-center" style="padding-left: {{ $indent }}rem;">
            @if($level > 0)
                <i class="fas fa-level-up-alt fa-rotate-90 text-muted me-2" style="font-size: 0.75rem;"></i>
            @else
                <i class="fas fa-folder{{ $category->children->isNotEmpty() ? '' : '-open' }} text-primary me-2"></i>
            @endif
            <div>
                <a href="{{ route('categories.show', $category) }}" class="fw-semibold text-dark">
                    {{ $category->name }}
                </a>
                @if($category->slug)
                    <div class="text-muted" style="font-size: 0.75rem;">{{ $category->slug }}</div>
                @endif
            </div>
        </div>
    </td>
    <td class="d-none d-md-table-cell text-muted">
        {{ Str::limit($category->description, 60) ?? '—' }}
    </td>
    <td class="text-center">
        <span class="badge bg-light text-dark">{{ $category->products_count }}</span>
    </td>
    <td class="text-center">
        <form action="{{ route('categories.toggle-status', $category) }}" method="POST" class="d-inline">
            @csrf
            @if($category->is_active)
                <span class="badge bg-success" role="button" onclick="this.closest('form').submit()" title="Cliquer pour désactiver">
                    <i class="fas fa-check me-1"></i>Actif
                </span>
            @else
                <span class="badge bg-secondary" role="button" onclick="this.closest('form').submit()" title="Cliquer pour activer">
                    <i class="fas fa-times me-1"></i>Inactif
                </span>
            @endif
        </form>
    </td>
    <td class="text-center d-none d-md-table-cell">
        @if($category->is_visible_online)
            <i class="fas fa-globe text-success" title="Visible en ligne"></i>
        @else
            <i class="fas fa-eye-slash text-muted" title="Masqué en ligne"></i>
        @endif
    </td>
    <td class="text-end">
        <div class="btn-group btn-group-sm">
            <a href="{{ route('categories.show', $category) }}" class="btn btn-outline-secondary" title="Voir">
                <i class="fas fa-eye"></i>
            </a>
            <a href="{{ route('categories.edit', $category) }}" class="btn btn-outline-primary" title="Modifier">
                <i class="fas fa-edit"></i>
            </a>
            <form action="{{ route('categories.destroy', $category) }}" method="POST" class="d-inline"
                  id="delete-category-{{ $category->id }}">
                @csrf
                @method('DELETE')
                <button type="button" class="btn btn-outline-danger" title="Supprimer"
                        onclick="confirmDelete('delete-category-{{ $category->id }}', 'Supprimer la catégorie « {{ addslashes($category->name) }} » ?')">
                    <i class="fas fa-trash"></i>
                </button>
            </form>
        </div>
    </td>
</tr>
@if($category->children->isNotEmpty())
    @foreach($category->children as $child)
        @include('products.categories._row', ['category' => $child, 'level' => $level + 1])
    @endforeach
@endif
