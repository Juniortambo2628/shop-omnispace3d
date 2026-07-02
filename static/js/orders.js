/* orders.js — order management functions for admin orders page */

function toggleDetail(orderId) {
  var row   = document.getElementById('detail_' + orderId);
  var arrow = document.getElementById('arrow_' + orderId);
  var open  = row.style.display === 'table-row';
  row.style.display   = open ? 'none'       : 'table-row';
  arrow.textContent   = open ? '▶'          : '▼';
}

function setStatus(orderId, status) {
  var labels = {
    'Approved': { title: 'Approve Order?', text: 'This will mark the order as approved.', icon: 'question', confirm: 'Yes, Approve' },
    'Invoiced': { title: 'Send Invoice?', text: 'This will mark as invoiced and send the payment confirmation email.', icon: 'info', confirm: 'Yes, Invoice' },
    'Fulfilled': { title: 'Mark Fulfilled?', text: 'This will close the order as fulfilled.', icon: 'success', confirm: 'Yes, Fulfilled' },
    'Cancelled': { title: 'Cancel Order?', text: 'This action cannot be undone. The order will be cancelled.', icon: 'warning', confirm: 'Yes, Cancel It', danger: true }
  };
  var cfg = labels[status] || { title: 'Set status to ' + status + '?', text: '', icon: 'question', confirm: 'Confirm' };
  
  OmniConfirm(cfg).then((result) => {
    if (!result.isConfirmed) return;
    fetch('/admin/orders/' + orderId + '/status', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ status: status })
    }).then(res => res.json()).then(data => {
      if (data.success) {
        OmniToast('Status updated to ' + status, 'success');
        setTimeout(() => htmx.ajax('GET', location.href, {target:'#admin-content'}), 1000);
      } else {
        OmniToast(data.error || 'Error updating status', 'error');
      }
    });
  });
}

function savePayRef(orderId) {
  var ref = document.getElementById('payref_' + orderId).value.trim();
  fetch('/admin/orders/' + orderId + '/payment-reference', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ payment_reference: ref })
  }).then(() => OmniToast('Payment reference saved', 'success'));
}

function sendInvoice(orderId) {
  OmniConfirm({
    title: 'Re-send Invoice?',
    text: 'This will re-send the order invoice email to the client.',
    icon: 'question',
    confirm: 'Yes, Send It'
  }).then((result) => {
    if (!result.isConfirmed) return;
    fetch('/admin/orders/' + orderId + '/send-invoice', { method: 'POST' })
      .then(() => OmniToast('Invoice email sent', 'success'));
  });
}

function verifyPayment(orderId, status, clientRef) {
  var titles = {
    'verified': { title: 'Verify Payment?', text: 'This will mark the payment as verified.', icon: 'success', confirm: 'Yes, Verify' },
    'rejected': { title: 'Reject Payment?', text: 'This will mark the payment as rejected.', icon: 'warning', confirm: 'Yes, Reject', danger: true }
  };
  var cfg = titles[status] || { title: 'Update verification?', text: '', icon: 'question', confirm: 'Confirm' };

  OmniConfirm(cfg).then((result) => {
    if (!result.isConfirmed) return;
    var body = { status: status };
    if (clientRef !== undefined && clientRef !== '') body.client_payment_reference = clientRef;
    fetch('/admin/orders/' + orderId + '/verify-payment', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body)
    }).then(res => res.json()).then(data => {
      if (data.success) {
        OmniToast('Payment ' + status, 'success');
        setTimeout(() => htmx.ajax('GET', location.href, {target:'#admin-content'}), 1000);
      } else {
        OmniToast(data.error || 'Error updating verification', 'error');
      }
    });
  });
}
