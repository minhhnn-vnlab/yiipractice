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

        #cancel-2fa {
            display: none;
            margin-top: 20px;
            padding: 10px;
            background-color: #f0f0f0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .disabled {
            pointer-events: none;
            opacity: 0.6;
        }

        #notification {
            background-color: burlywood;
            padding: 12px;
            margin-bottom: 12px;
        }

        .span-cancel {
            display: none;
            background-color: burlywood;
            padding: 12px;
            margin-bottom: 12px;
        }
    </style>
</head>

<body>
    <h1>Setup Two-Factor Authentication</h1>
    <div id="notification" style="display: none;">
        Hiện tại tài khoản của bạn đang setup xác thực 2FA bằng <span id="current-method"></span>
    </div>
    <div class="span-cancel">
        Hiện tại tài khoản của bạn chưa bật xác thực 2 lớp. Hãy bật xác thực 2 lớp để bảo mật tài khoản của bạn tốt hơn !!!
    </div>
    <div>
        <label class="checkbox-label">
            <input type="radio" name="two_fa_method" value="authenticator" id="authenticator-method">
            Xác thực 2FA bằng Authenticator
        </label>
        <label class="checkbox-label">
            <input type="radio" name="two_fa_method" value="email" id="email-method">
            Xác thực 2FA bằng Email
        </label>
        <label class="checkbox-label cancel-label">
            <input type="radio" name="two_fa_method" value="cancel" id="cancel-method">
            Hủy Xác thực 2FA
        </label>
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

    <div id="cancel-2fa">
        <p>Bạn có muốn hủy xác thực 2FA?.</p>
        <form id="confirm-form-cancel" action="api/user/confirm-2fa" method="post">
            <input type="hidden" name="user_id" value='<?= $user->id ?>'>
            <input type="hidden" name="two_fa_method" value="">
            <input type="hidden" name="code" value="">
            <button type="submit">Confirm</button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var method = '<?= $method ?>';
            var notification = document.getElementById('notification');
            var currentMethod = document.getElementById('current-method');

            if (method === 'authenticator' || method === 'email') {
                var radio = document.getElementById(method + '-method');
                var label = radio.parentElement;
                var checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.checked = true;
                checkbox.disabled = true;
                checkbox.classList.add('disabled');

                checkbox.name = radio.name;
                checkbox.value = radio.value;
                checkbox.id = radio.id;

                label.insertBefore(checkbox, radio);
                label.removeChild(radio);

                notification.style.display = 'block';
                currentMethod.textContent = method === 'authenticator' ? 'Authenticator' : 'Email';
            } else {
                document.querySelector('.cancel-label').style.display = 'none';
                document.querySelector('.span-cancel').style.display = 'block';
            }
            const qrCodeContainer = document.getElementById('qr-code-container');
            const qrCodeImage = document.getElementById('qr-code-image');
            const confirmFormAuthenticator = document.getElementById('confirm-form-authenticator');
            const confirmFormEmail = document.getElementById('confirm-form-email');
            const emailNotification = document.getElementById('email-notification');
            const confirmCacel2FA = document.getElementById('confirm-form-cancel');
            const cancelForm = document.getElementById('cancel-2fa');

            if (document.getElementById('authenticator-method').type == 'radio') {
                document.getElementById('authenticator-method').addEventListener('click', function() {
                    fetch('/api/user/get-qr-code?id=<?= $user->id ?>')
                        .then(response => response.json())
                        .then(data => {
                            const svgData = data.qrCode;
                            const dataUri = `data:image/svg+xml;base64,${btoa(svgData)}`;
                            qrCodeImage.src = dataUri;
                            qrCodeContainer.style.display = 'block';
                            emailNotification.style.display = 'none';
                            cancelForm.style.display = 'none';
                        })
                        .catch(error => console.error('Error:', error));
                });
            }
            if (document.getElementById('email-method').type == 'radio') {
                document.getElementById('email-method').addEventListener('click', function() {
                    qrCodeContainer.style.display = 'none';
                    emailNotification.style.display = 'block';
                    cancelForm.style.display = 'none';
                    fetch('/api/user/sendCodeEmail?id=<?= $user->id ?>')
                        .then(response => response.json())
                        .then(data => {})
                        .catch(error => console.error('Error:', error));
                });
            }
            if (document.getElementById('cancel-method').type == 'radio') {
                document.getElementById('cancel-method').addEventListener('click', function() {
                    qrCodeContainer.style.display = 'none';
                    emailNotification.style.display = 'none';
                    cancelForm.style.display = 'block'
                });
            }

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
            confirmCacel2FA.addEventListener('submit', function(event) {
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