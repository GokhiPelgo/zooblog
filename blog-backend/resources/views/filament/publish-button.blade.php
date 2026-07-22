<div style="display:inline-flex; align-items:center; gap:.5rem; margin-right:.5rem;">
    <span data-zb-msg
          style="font-size:.8rem; font-weight:600; max-width:32rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"></span>

    <button type="button" data-zb-btn
        style="display:inline-flex; align-items:center; gap:.4rem;
               background:#059669; color:#fff; font-weight:600; font-size:.875rem;
               padding:.5rem .9rem; border:none; border-radius:.5rem; cursor:pointer;">
        🚀 <span data-zb-label>Publicar</span>
    </button>
</div>

<script>
(function () {
    var root  = document.currentScript.previousElementSibling;
    var btn   = root.querySelector('[data-zb-btn]');
    if (!btn || btn.dataset.wired) return;   // evita doble enganche con Livewire
    btn.dataset.wired = '1';

    var msg   = root.querySelector('[data-zb-msg]');
    var label = root.querySelector('[data-zb-label]');
    var TOKEN = '{{ csrf_token() }}';
    var PUBLISH_URL = '{{ url('/publish') }}';
    var STATUS_URL  = '{{ url('/publish/status') }}';
    var timer = null;

    function setMsg(text, color) { msg.textContent = text || ''; msg.style.color = color || '#6b7280'; }
    function busy(on) {
        btn.disabled = on;
        btn.style.opacity = on ? '.7' : '1';
        btn.style.cursor = on ? 'wait' : 'pointer';
        label.textContent = on ? 'Compilando…' : 'Publicar';
    }

    // Devuelve true si conviene seguir consultando (estado "building").
    function render(s) {
        if (!s || s.state === 'idle') { setMsg('', ''); busy(false); return false; }
        if (s.state === 'building')   { setMsg('⏳ ' + (s.message || 'Compilando…'), '#6b7280'); busy(true);  return true;  }
        if (s.state === 'done')       { setMsg(s.message, '#059669'); busy(false); return false; }
        if (s.state === 'error')      { setMsg(s.message, '#dc2626'); busy(false); return false; }
        return false;
    }

    function stopPolling() { if (timer) { clearInterval(timer); timer = null; } }
    function startPolling() { if (!timer) { timer = setInterval(poll, 2500); } }

    function poll() {
        fetch(STATUS_URL, { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (s) { if (render(s)) startPolling(); else stopPolling(); })
            .catch(function () {});
    }

    function publish() {
        if (!confirm('¿Estás seguro de que quieres publicar los cambios?')) return;
        busy(true); setMsg('⏳ Enviando…', '#6b7280');
        fetch(PUBLISH_URL, { method: 'POST', headers: { 'X-CSRF-TOKEN': TOKEN, 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (s) { if (render(s)) startPolling(); })
            .catch(function () { setMsg('✗ No se pudo iniciar la publicación.', '#dc2626'); busy(false); });
    }

    btn.addEventListener('click', publish);

    // Al cargar, revisa si ya hay un build en curso y retoma el aviso.
    poll();
})();
</script>
