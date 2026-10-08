window.GRAPI = (() => {
  let csrf = sessionStorage.getItem('gr_csrf') || '';
  const setCsrf = v => { csrf = v || ''; if (csrf) sessionStorage.setItem('gr_csrf', csrf); else sessionStorage.removeItem('gr_csrf'); };
  async function request(url, options = {}) {
    const headers = new Headers(options.headers || {});
    if (!(options.body instanceof FormData) && options.body && !headers.has('Content-Type')) headers.set('Content-Type','application/json');
    if (csrf && (options.method || 'GET').toUpperCase() !== 'GET') headers.set('X-CSRF-Token', csrf);
    const res = await fetch(url, { credentials:'same-origin', ...options, headers });
    let data = {}; try { data = await res.json(); } catch (_) {}
    if (!res.ok) throw new Error(data.error || `Request failed (${res.status})`);
    if (data.csrf) setCsrf(data.csrf);
    return data;
  }
  return { request, setCsrf, getCsrf:()=>csrf };
})();
