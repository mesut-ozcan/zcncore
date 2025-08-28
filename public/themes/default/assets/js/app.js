// ZCNCore default theme (v3.2.1)

(function(){
  // 🔔 Flash mesajlarını birkaç saniye sonra yumuşak gizle
  const alerts = document.querySelectorAll('.alert');
  alerts.forEach(a=>{
    setTimeout(()=>{ a.style.transition='opacity .4s'; a.style.opacity='0'; }, 3500);
    setTimeout(()=>{ a.remove(); }, 4200);
  });
  console.log('ZCNCore theme JS loaded');
})();

/*
 * Opsiyonel: CSRF auto-refresh fonksiyonu
 * 
 * Not: Çekirdekte CSRF cookie tabanlı olduğu için normal form submit’lerde gerek yok.
 * SPA / AJAX ağırlıklı projelerde istersen bu fonksiyonu çağırarak token yenileyebilirsin.
 *
 * Kullanım:
 *   await window.zcn.refreshCsrf();
 */
window.zcn = window.zcn || {};
window.zcn.refreshCsrf = async function () {
  try {
    const res = await fetch('/csrf/refresh', {
      method: 'POST',
      headers: { 'Accept': 'application/json' }
    });
    const json = await res.json();
    if (json && json.ok && json.token) {
      // Sayfadaki tüm form’lardaki _token inputlarını güncelle
      document.querySelectorAll('input[name="_token"]').forEach(i => i.value = json.token);
      return json.token;
    }
  } catch (e) {
    console.warn('CSRF refresh error', e);
  }
  return null;
};