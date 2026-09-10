<?php

namespace App\Console\Commands;

use App\Models\ScanQueue;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class ScannerWebSocketHandler implements MessageComponentInterface
{
    protected $clients;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);

        $path = $conn->httpRequest->getUri()->getPath();

        // Serve scanner HTML page for HTTP requests (non-WebSocket)
        if ($conn->httpRequest->getHeader('Upgrade') !== 'websocket') {
            $conn->send($this->getScannerHtml());
            $conn->close();
            return;
        }

        // WebSocket connection — send ack
        $conn->send('CONNECTED_ACK');
        $this->log("Client connected: {$conn->resourceId}");
    }

    public function onMessage(ConnectionInterface $from, $data)
    {
        $barcode = trim((string) $data);

        if (empty($barcode)) return;

        $this->log("Barcode received: {$barcode}");

        // Store in scan_queue for POS to pick up
        ScanQueue::create([
            'barcode' => $barcode,
            'status' => 'pending',
            'ip_address' => $from->httpRequest->getServerParams()['REMOTE_ADDR'] ?? null,
        ]);

        // Broadcast to all other connected clients (if any)
        foreach ($this->clients as $client) {
            if ($client !== $from) {
                $client->send("SCAN:{$barcode}");
            }
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        $this->log("Client disconnected: {$conn->resourceId}");
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        $this->log("Error: {$e->getMessage()}");
        $conn->close();
    }

    protected function log(string $msg): void
    {
        $time = date('Y-m-d H:i:s');
        echo "[{$time}] {$msg}\n";
    }

    protected function getScannerHtml(): string
    {
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
<title>HALIS Scanner</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:#0f172a;color:#e2e8f0;min-height:100vh;display:flex;flex-direction:column;align-items:center;padding:20px}
.logo{font-size:24px;font-weight:800;color:#6366f1;margin-bottom:8px;letter-spacing:1px}
.status{padding:10px 20px;border-radius:8px;font-size:14px;font-weight:600;margin-bottom:20px;text-align:center}
.status.disconnected{background:#7f1d1d;color:#fca5a5}
.status.connected{background:#14532d;color:#86efac}
.status.scanning{background:#1e3a5f;color:#93c5fd}
.card{background:#1e293b;border-radius:16px;padding:24px;width:100%;max-width:400px;margin-bottom:16px;border:1px solid #334155}
.card h3{font-size:14px;color:#94a3b8;margin-bottom:12px;text-transform:uppercase;letter-spacing:1px}
.input-row{display:flex;gap:8px}
.input-row input{flex:1;padding:14px;border-radius:10px;border:2px solid #334155;background:#0f172a;color:#fff;font-size:16px;outline:none}
.input-row input:focus{border-color:#6366f1}
.input-row button{padding:14px 20px;border-radius:10px;border:none;background:#6366f1;color:#fff;font-size:14px;font-weight:700;cursor:pointer}
.input-row button:active{transform:scale(0.97)}
.btn-scan{width:100%;padding:16px;border-radius:12px;border:2px solid #334155;background:#0f172a;color:#e2e8f0;font-size:16px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:10px}
.btn-scan:active{background:#1e293b}
.btn-scan.active{border-color:#6366f1;background:#1e1b4b;color:#a5b4fc}
#reader{width:100%;border-radius:12px;overflow:hidden;margin-top:12px;display:none}
#reader video{border-radius:12px}
.toast{position:fixed;top:20px;left:50%;transform:translateX(-50%);padding:12px 24px;border-radius:10px;font-weight:600;font-size:14px;z-index:9999;animation:fadeInOut 2.5s ease forwards}
.toast.success{background:#166534;color:#bbf7d0}
.toast.error{background:#991b1b;color:#fecaca}
@keyframes fadeInOut{0%{opacity:0;transform:translateX(-50%) translateY(-10px)}10%{opacity:1;transform:translateX(-50%) translateY(0)}80%{opacity:1}100%{opacity:0}}
.history{margin-top:8px;max-height:150px;overflow-y:auto}
.history-item{padding:8px 12px;border-bottom:1px solid #334155;font-size:13px;display:flex;justify-content:space-between}
.history-item .code{color:#a5b4fc;font-weight:600}
.history-item .time{color:#64748b}
</style>
</head>
<body>
<div class="logo">HALIS SCANNER</div>
<div id="statusBox" class="status disconnected">Disconnected from POS</div>

<div class="card">
<h3>Manual Entry</h3>
<div class="input-row">
<input type="text" id="barcodeInput" placeholder="Type barcode number..." inputmode="numeric">
<button onclick="sendManual()">Send</button>
</div>
</div>

<div class="card">
<button class="btn-scan" id="scanBtn" onclick="toggleCamera()">
<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 7V5a2 2 0 0 1 2-2h2"/><path d="M17 3h2a2 2 0 0 1 2 2v2"/><path d="M21 17v2a2 2 0 0 1-2 2h-2"/><path d="M7 21H5a2 2 0 0 1-2-2v-2"/><line x1="7" y1="12" x2="17" y2="12"/></svg>
<span id="scanBtnText">Start Camera Scanner</span>
</button>
<div id="reader"></div>
</div>

<div class="card">
<h3>Recent Scans</h3>
<div class="history" id="history"></div>
</div>

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
var ws, reconnectTimer, scanner, scanning = false;
var HOST = location.hostname;
var PORT = location.port || "8432";

function connect() {
    ws = new WebSocket("ws://" + HOST + ":" + PORT + "/ws");
    ws.onopen = function() {
        document.getElementById("statusBox").className = "status connected";
        document.getElementById("statusBox").textContent = "Connected to POS Desktop";
    };
    ws.onmessage = function(e) {
        if (e.data === "CONNECTED_ACK") return;
        showToast("Received: " + e.data, "success");
    };
    ws.onclose = function() {
        document.getElementById("statusBox").className = "status disconnected";
        document.getElementById("statusBox").textContent = "Disconnected — reconnecting...";
        reconnectTimer = setTimeout(connect, 2000);
    };
    ws.onerror = function() {};
}

function sendBarcode(code) {
    if (!ws || ws.readyState !== 1) return;
    ws.send(code);
    addHistory(code);
    showToast("Sent: " + code, "success");
}

function sendManual() {
    var input = document.getElementById("barcodeInput");
    var val = input.value.trim();
    if (!val) return;
    sendBarcode(val);
    input.value = "";
}

document.getElementById("barcodeInput").addEventListener("keydown", function(e) {
    if (e.key === "Enter") { e.preventDefault(); sendManual(); }
});

function toggleCamera() {
    if (scanning) { stopCamera(); return; }
    var el = document.getElementById("reader");
    el.style.display = "block";
    scanner = new Html5QrcodeScanner("reader", { fps: 10, qrbox: 250, aspectRatio: 1.0 });
    scanner.render(function(decoded) {
        var debounce = false;
        if (!debounce) { debounce = true; sendBarcode(decoded); setTimeout(function(){ debounce = false; }, 2500); }
    });
    scanning = true;
    document.getElementById("scanBtnText").textContent = "Stop Camera";
    document.getElementById("scanBtn").className = "btn-scan active";
}

function stopCamera() {
    if (scanner) { scanner.clear(); document.getElementById("reader").style.display = "none"; }
    scanning = false;
    document.getElementById("scanBtnText").textContent = "Start Camera Scanner";
    document.getElementById("scanBtn").className = "btn-scan";
}

function addHistory(code) {
    var h = document.getElementById("history");
    var now = new Date();
    var t = now.getHours().toString().padStart(2,"0") + ":" + now.getMinutes().toString().padStart(2,"0") + ":" + now.getSeconds().toString().padStart(2,"0");
    h.innerHTML = '<div class="history-item"><span class="code">' + code + '</span><span class="time">' + t + '</span></div>' + h.innerHTML;
    if (h.children.length > 20) h.removeChild(h.lastChild);
}

function showToast(msg, type) {
    var t = document.createElement("div");
    t.className = "toast " + type;
    t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(function(){ t.remove(); }, 2500);
}

connect();
</script>
</body>
</html>
HTML;
    }
}
