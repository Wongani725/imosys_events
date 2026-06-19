<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Your OTP Code</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; margin: 0;">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td align="center">
            <table width="600" cellpadding="0" cellspacing="0" border="0" style="background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                <tr>
                    <td align="center" style="padding-bottom: 20px;">
                        <h1 style="color: #333333; font-size: 24px;">Your OTP Code</h1>
                        <p style="font-size: 16px; color: #666666;">Please use the following one-time password to proceed:</p>
                    </td>
                </tr>
                <tr>
                    <td align="center" style="padding: 20px 0;">
                        <div style="display: inline-block; padding: 12px 24px; background-color: #006198; color: #ffffff; font-size: 24px; border-radius: 5px; letter-spacing: 4px;">
                            {{ $otp }}
                        </div>
                    </td>
                </tr>
                <tr>
                    <td align="center" style="padding-top: 20px; color: #999999; font-size: 12px;">
                        <p>This code will expire in 3 minutes.</p>
                        <p>If you did not request this, please ignore this email.</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
