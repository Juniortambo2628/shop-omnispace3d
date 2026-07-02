/* product-colors.js — shared color row management for add/edit product */

function addColor() {
  var div = document.createElement('div');
  div.className = 'color-row';
  div.innerHTML = '<input type="text" name="color_name[]" placeholder="e.g. White">'
                + '<button type="button" class="remove-color" onclick="removeColor(this)">×</button>';
  document.getElementById('colors-container').appendChild(div);
}

function removeColor(btn) {
  var rows = document.querySelectorAll('.color-row');
  if (rows.length > 1) btn.parentElement.remove();
}

function togglePrice(cb) {
  var section = document.getElementById('price-section');
  if (section) section.style.display = cb.checked ? 'none' : '';
  var priceInput = document.getElementById('price');
  if (priceInput) priceInput.required = !cb.checked;
}
