@php
    $status = session('publish_status');
    // Verde si empieza con ✓, rojo si empieza con ✗, gris para el resto.
    $color = str_starts_with((string) $status, '✓') ? '#059669'
        : (str_starts_with((string) $status, '✗') ? '#dc2626' : '#6b7280');
@endphp

<div style="display:inline-flex; align-items:center; gap:.5rem; margin-right:.5rem;">
    @if ($status)
        <span style="font-size:.8rem; color:{{ $color }}; font-weight:600; max-width:32rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"
              title="{{ $status }}">
            {{ $status }}
        </span>
    @endif

    <form method="POST" action="{{ url('/publish') }}" style="margin:0;"
          onsubmit="if(!confirm('¿Estás seguro de que quieres publicar los cambios?')){return false;} var b=this.querySelector('button'); b.disabled=true; b.style.opacity=.7; b.style.cursor='wait'; b.querySelector('[data-label]').textContent='Compilando…';">
        @csrf
        <button type="submit"
            style="display:inline-flex; align-items:center; gap:.4rem;
                   background:#059669; color:#fff; font-weight:600; font-size:.875rem;
                   padding:.5rem .9rem; border:none; border-radius:.5rem; cursor:pointer;"
            onmouseover="if(!this.disabled)this.style.background='#047857'"
            onmouseout="if(!this.disabled)this.style.background='#059669'">
            🚀 <span data-label>Publicar</span>
        </button>
    </form>
</div>
