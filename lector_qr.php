<script src="node_modules/html5-qrcode/minified/html5-qrcode.min.js"></script>
<div id="reader"></div>
<script>
        var html5QrcodeScanner = new Html5QrcodeScanner("reader", { fps: 10, qrbox: 550 });
            function onScanSuccess(qrCodeMessage) {
                    // handle on success condition with the decoded message
                    alert(qrCodeMessage);
                    html5QrcodeScanner.clear();
                    // ^ this will stop the scanner (video feed) and clear the scan area.
            }

        html5QrcodeScanner.render(onScanSuccess);
</script>