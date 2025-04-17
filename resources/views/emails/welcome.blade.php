<!DOCTYPE html>
<html>
<head>
    <title>Bienvenido al SENA</title>
</head>
<body>
    <h1>¡Bienvenido {{ $user->nombres_completos }}!</h1>
    <p>Tu registro ha sido exitoso. A continuación, encontrarás tu código QR para el registro de entradas y salidas:</p>
    
    <div>
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data={{ $user->qr_code }}" alt="Tu código QR">
    </div>
    
    <p>Por favor, guarda este código QR ya que lo necesitarás para registrar tus entradas y salidas en el SENA.</p>
    
    <p>Tus credenciales de acceso son:</p>
    <ul>
        <li>Correo: {{ $user->correo }}</li>
        <li>Documento: {{ $user->documento_identidad }}</li>
    </ul>
    
    <p>Saludos,<br>
    Equipo SENA</p>
</body>
</html>