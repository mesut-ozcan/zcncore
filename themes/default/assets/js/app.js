document.addEventListener('DOMContentLoaded',()=>{ /* minimal */ });
window.zcn = window.zcn || {};
window.zcn.refreshCsrf = async function () {
  try {
    const res = await fetch('/csrf/refresh', { method: 'POST', headers: { 'Accept': 'application/json' }});
    const json = await res.json();
    if (json && json.ok && json.token) {
      // sayfadaki tüm form’lardaki _token inputlarını güncelle
      document.querySelectorAll('input[name="_token"]').forEach(i => i.value = json.token);
      return json.token;
    }
  } catch (e) {}
  return null;
};