/* ── Storefront PJAX Navigation ── */
document.addEventListener('DOMContentLoaded', function() {
  function loadPjaxPage(url, pushState) {
    if (pushState === undefined) pushState = true;
    var container = document.querySelector('.container');
    if (container) {
      container.style.opacity = '0.5';
      container.style.transition = 'opacity 0.15s ease';
    }
    fetch(url)
      .then(function(res) {
        if (res.redirected) return fetch(res.url).then(function(r) { return Promise.all([r.text(), res.url]); });
        return Promise.all([res.text(), url]);
      })
      .then(function(data) {
        var html = data[0], resolvedUrl = data[1];
        var parser = new DOMParser();
        var doc = parser.parseFromString(html, 'text/html');
        var newContainer = doc.querySelector('.container');
        var oldContainer = document.querySelector('.container');
        if (newContainer && oldContainer) {
          document.title = doc.title;
          oldContainer.outerHTML = newContainer.outerHTML;
          if (pushState) history.pushState({ pjax: true, url: resolvedUrl }, doc.title, resolvedUrl);
          window.scrollTo({ top: 0, behavior: 'instant' });
          var newScripts = document.querySelector('.container').querySelectorAll('script');
          newScripts.forEach(function(script) {
            var newScript = document.createElement('script');
            if (script.src) newScript.src = script.src; else newScript.textContent = script.textContent;
            document.body.appendChild(newScript);
            newScript.remove();
          });
          document.dispatchEvent(new Event('pjax:complete'));
        } else {
          window.location.href = resolvedUrl;
        }
      })
      .catch(function(err) { console.error('PJAX load failed:', err); window.location.href = url; });
  }

  function submitPjaxForm(form, actionPath) {
    var container = document.querySelector('.container');
    if (container) { container.style.opacity = '0.5'; container.style.transition = 'opacity 0.15s ease'; }
    var formData = new FormData(form);
    var method = (form.getAttribute('method') || 'GET').toUpperCase();
    var url = actionPath, fetchOptions = { method: method };
    if (method === 'GET') { url += '?' + new URLSearchParams(formData).toString(); } else { fetchOptions.body = formData; }
    fetch(url, fetchOptions)
      .then(function(res) {
        if (res.redirected) return fetch(res.url).then(function(r) { return Promise.all([r.text(), res.url]); });
        return Promise.all([res.text(), url]);
      })
      .then(function(data) {
        var html = data[0], resolvedUrl = data[1];
        var parser = new DOMParser();
        var doc = parser.parseFromString(html, 'text/html');
        var newContainer = doc.querySelector('.container');
        var oldContainer = document.querySelector('.container');
        if (newContainer && oldContainer) {
          document.title = doc.title;
          oldContainer.outerHTML = newContainer.outerHTML;
          history.pushState({ pjax: true, url: resolvedUrl }, doc.title, resolvedUrl);
          window.scrollTo({ top: 0, behavior: 'instant' });
          var newScripts = document.querySelector('.container').querySelectorAll('script');
          newScripts.forEach(function(script) {
            var newScript = document.createElement('script');
            if (script.src) newScript.src = script.src; else newScript.textContent = script.textContent;
            document.body.appendChild(newScript);
            newScript.remove();
          });
          document.dispatchEvent(new Event('pjax:complete'));
        } else { form.submit(); }
      })
      .catch(function(err) { console.error('PJAX form submit failed:', err); form.submit(); });
  }

  document.addEventListener('click', function(e) {
    var a = e.target.closest('a');
    if (!a || a.target === '_blank' || a.hasAttribute('download') || a.hostname !== window.location.hostname) return;
    var path = a.pathname + a.search + a.hash;
    if (path.startsWith('/order/')) { e.preventDefault(); loadPjaxPage(path); }
  });

  document.addEventListener('submit', function(e) {
    var form = e.target.closest('form');
    if (!form) return;
    var action = form.getAttribute('action') || window.location.pathname;
    var tempLink = document.createElement('a');
    tempLink.href = action;
    if (tempLink.hostname !== window.location.hostname || !tempLink.pathname.startsWith('/order/')) return;
    e.preventDefault();
    submitPjaxForm(form, tempLink.pathname);
  });

  window.addEventListener('popstate', function(e) {
    if (e.state && e.state.pjax) loadPjaxPage(e.state.url, false);
    else if (window.location.pathname.startsWith('/order/')) loadPjaxPage(window.location.pathname + window.location.search, false);
  });
});
