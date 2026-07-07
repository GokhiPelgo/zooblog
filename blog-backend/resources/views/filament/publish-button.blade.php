<div style="display:inline-flex; align-items:center; gap:.5rem; margin-right:.5rem;">
    @if (session('publish_status'))
        <span style="font-size:.8rem; color:#059669; font-weight:600;">
            {{ session('publish_status') }}
        </span>
    @endif

    <form method="POST" action="{{ url('/publish') }}" style="margin:0;">
        @csrf
        <button type="submit"
            style="display:inline-flex; align-items:center; gap:.4rem;
                   background:#059669; color:#fff; font-weight:600; font-size:.875rem;
                   padding:.5rem .9rem; border:none; border-radius:.5rem; cursor:pointer;"
            onmouseover="this.style.background='#047857'"
            onmouseout="this.style.background='#059669'">
            🚀 Publicar
        </button>
    </form>
</div>
