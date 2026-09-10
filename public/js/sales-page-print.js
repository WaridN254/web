function numberFormat(n) { return Math.round(n).toLocaleString(); }

function unwrap(raw) {
    if (!raw) return null;
    if (Array.isArray(raw)) return raw[0] || null;
    return raw;
}

function registerPrintListeners() {
    Livewire.on('printRefundReceipt', function () {
        var rd = unwrap(arguments[0]);
        if (!rd) return;
        var items = rd.items || [];
        var comp = rd.company || {};
        var currency = comp.currency || 'KSh';
        var itemsHtml = '';
        items.forEach(function (item) {
            itemsHtml += '<tr><td>' + (item.product_name || '') + '</td><td style="text-align:right">' + (item.quantity || 0) + '</td><td style="text-align:right">' + numberFormat(item.unit_price || 0) + '</td><td style="text-align:right">' + numberFormat(item.line_total || 0) + '</td></tr>';
        });
        var css = '*{margin:0;padding:0;box-sizing:border-box}body{font-family:\"Courier New\",monospace;padding:16px;font-size:11px;color:#000}.store{text-align:center;font-size:13px;font-weight:bold;margin-bottom:2px}.store-sub{text-align:center;font-size:10px;color:#666}h2{text-align:center;font-size:14px;margin:8px 0 4px}.sub{text-align:center;color:#666;font-size:10px;margin-bottom:8px}hr{border:none;border-top:1px dashed #ccc;margin:6px 0}table{width:100%;border-collapse:collapse}td{font-size:10px;padding:3px 0}.total{text-align:right;font-size:12px;font-weight:bold;margin-top:6px}.footer{text-align:center;margin-top:12px;font-size:9px;color:#888}.badge{display:inline-block;padding:1px 6px;border-radius:3px;font-size:9px;font-weight:bold}.badge{background:#dbeafe;color:#1d4ed8}.row{display:flex;justify-content:space-between}.bold{font-weight:700}@media print{body{padding:8px;font-size:10px}h2{font-size:13px}td{font-size:9px;padding:2px 0}.total{font-size:11px}}';
        var win = window.open('', '_blank', 'width=360,height=520');
        if (!win) return;
        win.document.write('<!DOCTYPE html><html><head><title>Refund Receipt</title><style>' + css + '</style></head><body>');
        win.document.write('<div class="store">' + (comp.name || '') + '</div>');
        if (comp.address) win.document.write('<div class="store-sub">' + comp.address + '</div>');
        if (comp.phone) win.document.write('<div class="store-sub">' + comp.phone + '</div>');
        win.document.write('<h2>REFUND RECEIPT</h2>');
        win.document.write('<div class="sub">' + (rd.reference || '') + '</div>');
        win.document.write('<hr>');
        win.document.write('<div class="row"><span>Receipt No.</span><span>' + (rd.reference || '') + '</span></div>');
        win.document.write('<div class="row"><span>Date</span><span>' + (rd.date || '') + '</span></div>');
        win.document.write('<div class="row"><span>Cashier</span><span>' + (rd.cashier || '') + '</span></div>');
        win.document.write('<div class="row"><span>Customer</span><span>' + (rd.customer || '') + '</span></div>');
        win.document.write('<div class="row"><span>Original Receipt</span><span>' + (rd.original_receipt || '') + '</span></div>');
        win.document.write('<div class="row"><span>Payment Type</span><span class="badge badge-red">' + ((rd.payment_type || '').toUpperCase()) + '</span></div>');
        win.document.write('<hr>');
        win.document.write('<table><thead><tr><td style="font-weight:bold;border-bottom:2px solid #000;padding-bottom:3px">Item</td><td style="font-weight:bold;border-bottom:2px solid #000;padding-bottom:3px;text-align:right">Qty</td><td style="font-weight:bold;border-bottom:2px solid #000;padding-bottom:3px;text-align:right">Price</td><td style="font-weight:bold;border-bottom:2px solid #000;padding-bottom:3px;text-align:right">Total</td></tr></thead><tbody>' + itemsHtml + '</tbody></table>');
        win.document.write('<hr>');
        win.document.write('<div class="total">REFUND TOTAL: ' + currency + ' ' + numberFormat(rd.total || 0) + '</div>');
        win.document.write('<hr>');
        win.document.write('<div class="footer">Thank you</div>');
        win.document.write('</body></html>');
        win.document.close();
        setTimeout(function () { win.print(); }, 400);
    });

    Livewire.on('printPaymentReceipt', function () {
        var rd = unwrap(arguments[0]);
        if (!rd) return;
        var items = rd.items || [];
        var comp = rd.company || {};
        var currency = comp.currency || 'KSh';
        var itemsHtml = '';
        items.forEach(function (item) {
            itemsHtml += '<tr><td>' + (item.name || '') + ' x ' + (item.quantity || 0) + '</td><td style="text-align:right">' + currency + ' ' + numberFormat(item.line_total || 0) + '</td></tr>';
        });
        var css = '*{margin:0;padding:0;box-sizing:border-box}body{font-family:\"Courier New\",monospace;padding:16px;font-size:11px;color:#000}.store{text-align:center;font-size:13px;font-weight:bold;margin-bottom:2px}.store-sub{text-align:center;font-size:10px;color:#666}h2{text-align:center;font-size:14px;margin:8px 0 4px}.sub{text-align:center;color:#666;font-size:10px;margin-bottom:8px}hr{border:none;border-top:1px dashed #ccc;margin:6px 0}table{width:100%;border-collapse:collapse}td{font-size:10px;padding:3px 0}.total{text-align:right;font-size:12px;font-weight:bold;margin-top:6px}.footer{text-align:center;margin-top:12px;font-size:9px;color:#888}.badge{display:inline-block;padding:1px 6px;border-radius:3px;font-size:9px;font-weight:bold}.badge-red{background:#fee2e2;color:#dc2626}.badge-blue{background:#dbeafe;color:#1d4ed8}.row{display:flex;justify-content:space-between}.bold{font-weight:700}@media print{body{padding:8px;font-size:10px}h2{font-size:13px}td{font-size:9px;padding:2px 0}.total{font-size:11px}}';
        var win = window.open('', '_blank', 'width=360,height=520');
        if (!win) return;
        win.document.write('<!DOCTYPE html><html><head><title>Payment Receipt</title><style>' + css + '</style></head><body>');
        win.document.write('<div class="store">' + (comp.name || '') + '</div>');
        if (comp.address) win.document.write('<div class="store-sub">' + comp.address + '</div>');
        if (comp.phone) win.document.write('<div class="store-sub">' + comp.phone + '</div>');
        win.document.write('<h2>PAYMENT RECEIPT</h2>');
        win.document.write('<div class="sub">' + (rd.reference || '') + '</div>');
        win.document.write('<hr>');
        win.document.write('<div class="row"><span>Receipt No.</span><span>' + (rd.reference || '') + '</span></div>');
        win.document.write('<div class="row"><span>Customer</span><span>' + (rd.customer || '') + '</span></div>');
        win.document.write('<div class="row"><span>Invoice</span><span>#' + (rd.invoice || '') + '</span></div>');
        win.document.write('<div class="row"><span>Invoice Date</span><span>' + (rd.invoice_date || '') + '</span></div>');
        win.document.write('<div class="row"><span>Payment Method</span><span class="badge badge-blue">' + ((rd.payment_method || '').toUpperCase()) + '</span></div>');
        win.document.write('<hr>');
        win.document.write('<table>' + itemsHtml + '</table>');
        win.document.write('<hr>');
        win.document.write('<div class="row"><span>Invoice Total</span><span>' + currency + ' ' + numberFormat(rd.total || 0) + '</span></div>');
        win.document.write('<div class="row"><span>Total Paid</span><span>' + currency + ' ' + numberFormat(rd.paid_total || 0) + '</span></div>');
        win.document.write('<div class="row bold" style="margin-top:4px;font-size:12px"><span>Amount Received</span><span>' + currency + ' ' + numberFormat(rd.amount || 0) + '</span></div>');
        win.document.write('<div class="row"><span>' + (rd.remaining > 0 ? 'Balance Remaining' : 'Balance Cleared') + '</span><span>' + (rd.remaining > 0 ? currency + ' ' + numberFormat(rd.remaining) : '\u2014') + '</span></div>');
        win.document.write('<hr>');
        win.document.write('<div class="footer">Thank you for your payment.</div>');
        win.document.write('</body></html>');
        win.document.close();
        setTimeout(function () { win.print(); }, 400);
    });
}

if (window.Livewire) {
    registerPrintListeners();
} else {
    document.addEventListener('livewire:init', registerPrintListeners);
}