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
        #email-notification {
            display: none;
            margin-top: 20px;
            padding: 10px;
            background-color: #f0f0f0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <h1>Setup Two-Factor Authentication</h1>

    <div>
        <label>
            <input type="radio" name="two_fa_method" value="authenticator" id="authenticator-method"> Xác thực 2FA bằng Authenticator
            <input type="radio" name="two_fa_method" value="email" id="mail-method"> Xác thực 2FA bằng Email
        </label>
        <!-- Add other methods here if needed -->
    </div>

    <div id="qr-code-container">
        <img id="qr-code-image" src="" alt="QR Code">
        <form id="confirm-form-authenticator" action="api/user/confirm-2fa" method="post">
            <input type="hidden" name="user_id" value='<?= $user->id ?>'>
            <input type="hidden" name="two_fa_method" value="authenticator">
            <input type="text" name="code" placeholder="Enter code" required>
            <button type="submit">Confirm</button>
        </form>
    </div>

    <div id="email-notification">
        <p>Một mã code đã được gửi vào hộp thư email của bạn, hãy nhập mã code để bật xác thực 2FA bằng Email.</p>
        <form id="confirm-form-email" action="api/user/confirm-2fa" method="post">
            <input type="hidden" name="user_id" value='<?= $user->id ?>'>
            <input type="hidden" name="two_fa_method" value="email">
            <input type="text" name="code" placeholder="Enter code" required>
            <button type="submit">Confirm</button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
        const qrCodeContainer = document.getElementById('qr-code-container');
        const qrCodeImage = document.getElementById('qr-code-image');
        const confirmFormAuthenticator = document.getElementById('confirm-form-authenticator');
        const confirmFormEmail = document.getElementById('confirm-form-email');
        const emailNotification = document.getElementById('email-notification');

        document.getElementById('authenticator-method').addEventListener('click', function() {
            fetch('/api/user/get-qr-code?id=<?= $user->id ?>')
                .then(response => response.json())
                .then(data => {
                    const svgData = data.qrCode;
                    const dataUri = `data:image/svg+xml;base64,${btoa(svgData)}`;
                    qrCodeImage.src = dataUri;
                    qrCodeContainer.style.display = 'block';
                    emailNotification.style.display = 'none';
                })
                .catch(error => console.error('Error:', error));
        });

        document.getElementById('mail-method').addEventListener('click', function() {
            qrCodeContainer.style.display = 'none';
            emailNotification.style.display = 'block';
            fetch('/api/user/sendCodeEmail?id=<?= $user->id ?>')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 200) {
                        console.log('Email sent successfully');
                    } else {
                        console.error('Error:', data.message);
                    }
                })
                .catch(error => console.error('Error:', error));
        });

        confirmFormAuthenticator.addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(this);
            fetch('/api/user/confirm-2fa', {
                method: 'POST',
                body: formData,
            })
            .then(response => response.json())
            .then(data => {
                if (data.data) {
                    alert(data.message);
                    // Optionally, redirect the user to another page
                    window.location.href = '/site/index';
                } else {
                    alert('Error: ' + data.error);
                }
            })
            .catch(error => console.error('Error:', error));
        });

        confirmFormEmail.addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(this);
            fetch('/api/user/confirm-2fa', {
                method: 'POST',
                body: formData,
            })
            .then(response => response.json())
            .then(data => {
                if (data.data) {
                    alert(data.message);
                    // Optionally, redirect the user to another page
                    window.location.href = '/site/index';
                } else {
                    alert('Error: ' + data.error);
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });
    </script>
</body>
</html>