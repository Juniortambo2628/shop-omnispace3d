/* ── Shared JS Utilities ── esc(), showToast(), fuzzy search, color helpers ── */

function esc(s) {
  if (!s) return '';
  var d = document.createElement('div');
  d.textContent = s;
  return d.innerHTML;
}

function showToast(msg) {
  var t = document.getElementById('toast');
  if (!t) return;
  t.textContent = msg;
  t.classList.add('show');
  setTimeout(function() { t.classList.remove('show'); }, 2500);
}

/* Fuzzy search: score a single field against a term */
function fuzzyScore(haystack, term, weight) {
  haystack = String(haystack || '').toLowerCase().trim();
  term = String(term || '').toLowerCase().trim();
  if (!term || !haystack) return 0;
  if (haystack.indexOf(term) === 0) return (weight * 1000) + Math.max(0, 50 - term.length);
  if (haystack.indexOf(term) !== -1) return (weight * 500) + Math.max(0, 50 - term.length);
  var i = 0;
  for (var j = 0; j < haystack.length && i < term.length; j++) {
    if (haystack.charAt(j) === term.charAt(i)) i++;
  }
  if (i === term.length) return weight * 100;
  return 0;
}

/* Select a color chip and swap the card image */
function selectColorChip(el) {
  var siblings = el.parentElement.querySelectorAll('.color-chip');
  for (var i = 0; i < siblings.length; i++) siblings[i].classList.remove('selected');
  el.classList.add('selected');
}

/* Build a status badge HTML */
function statusBadge(status) {
  return '<span class="badge badge-' + esc(status || 'Pending') + '">' + esc(status || 'Pending') + '</span>';
}
