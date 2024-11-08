<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup 2FA</title>
    <style>
        #qr-code-container {
            display: none;
            margin-top: 20px;
        }
        #qr-code-image {
            border: 1px solid #ccc;
        }
    </style>
</head>
<body>
    <h1>Setup Two-Factor Authentication</h1>

    <div>
        <label>
            <input type="radio" name="method" value="authenticator" id="authenticator-method"> Authenticator App
        </label>
        <!-- Add other methods here if needed -->
    </div>

    <div id="qr-code-container">
        <img id="qr-code-image" src="" alt="QR Code">
        <form id="confirm-form" action="/user/confirm-2fa" method="post">
            <input type="hidden" name="method" value="authenticator">
            <input type="text" name="code" placeholder="Enter code" required>
            <button type="submit">Confirm</button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const qrCodeContainer = document.getElementById('qr-code-container');
            const qrCodeImage = document.getElementById('qr-code-image');
            const confirmForm = document.getElementById('confirm-form');

            document.getElementById('authenticator-method').addEventListener('click', function() {
                fetch('http://y2aa.test:8080/api/user/get-qr-code?id=<?= $user->id ?>')
                    .then(response => response.json())
                    .then(data => {
                        qrCodeImage.src = data.qrCode;
                        qrCodeContainer.style.display = 'block';
                    })
                    .catch(error => console.error('Error:', error));
            });

            confirmForm.addEventListener('submit', function(event) {
                event.preventDefault();
                const formData = new FormData(this);
                fetch('/user/confirm-2fa', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('2FA setup successful!');
                        // Optionally, redirect the user to another page
                        window.location.href = '/dashboard';
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => console.error('Error:', error));
            });
        });
    </script>
</body>
</html>