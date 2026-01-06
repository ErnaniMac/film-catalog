<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bem-vindo ao Cine Catálogo</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%); padding: 30px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="color: white; margin: 0;">Cine Catálogo</h1>
    </div>
    
    <div style="background: #f9f9f9; padding: 30px; border-radius: 0 0 8px 8px;">
        <h2 style="color: #1976d2; margin-top: 0;">Bem-vindo, {{ $user->name }}!</h2>
        
        <p>Obrigado por se cadastrar no Cine Catálogo. Estamos felizes em tê-lo conosco!</p>
        
        <p>Para começar a usar sua conta, por favor, verifique seu endereço de e-mail clicando no botão abaixo:</p>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $verificationUrl }}" style="background: #1976d2; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;">
                Verificar E-mail
            </a>
        </div>
        
        <p style="font-size: 12px; color: #666; margin-top: 30px;">
            Se o botão não funcionar, copie e cole o seguinte link no seu navegador:<br>
            <a href="{{ $verificationUrl }}" style="color: #1976d2; word-break: break-all;">{{ $verificationUrl }}</a>
        </p>
        
        <p style="font-size: 12px; color: #666; margin-top: 20px;">
            Este link expira em 24 horas. Se você não criou esta conta, pode ignorar este e-mail.
        </p>
    </div>
    
    <div style="text-align: center; margin-top: 20px; color: #999; font-size: 12px;">
        <p>© {{ date('Y') }} Cine Catálogo. Todos os direitos reservados.</p>
    </div>
</body>
</html>
