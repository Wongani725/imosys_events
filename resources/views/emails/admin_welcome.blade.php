<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Welcome to IIA Malawi</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; margin: 0;">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td align="center">
            <table width="600" cellpadding="0" cellspacing="0" border="0" style="background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                <tr>
                    <td align="center" style="padding-bottom: 5px;">
                        <h1 style="color: #006198; font-size: 22px; margin-bottom: 4px;">IIA Malawi</h1>
                        <p style="color: #888888; font-size: 13px; margin-top: 0;">Institute of Internal Auditors Malawi</p>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 10px 0 20px;">
                        <hr style="border: none; border-top: 1px solid #eeeeee;">
                    </td>
                </tr>
                <tr>
                    <td style="padding-bottom: 10px;">
                        <h2 style="color: #333333; font-size: 20px;">Welcome, {{ $name }}!</h2>
                        <p style="font-size: 15px; color: #555555; line-height: 1.6;">
                            Your admin account for the <strong>IIA Malawi Event Management System</strong> has been created.
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 15px 0;">
                        <table width="100%" cellpadding="12" cellspacing="0" border="0" style="background-color: #f9f9f9; border-radius: 6px;">
                            <tr>
                                <td style="font-size: 14px; color: #333333;">
                                    <strong>Role:</strong> {{ $role }}
                                </td>
                            </tr>
                            <tr>
                                <td style="font-size: 14px; color: #333333;">
                                    <strong>Email:</strong> {{ $email }}
                                </td>
                            </tr>
                            <tr>
                                <td style="font-size: 14px; color: #333333;">
                                    <strong>Temporary Password:</strong>
                                    <span style="font-family: monospace; font-size: 16px; background: #fff; padding: 2px 8px; border: 1px solid #ddd; border-radius: 3px;">{{ $password }}</span>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td align="center" style="padding: 20px 0;">
                        <a href="{{ $loginUrl }}"
                           style="display: inline-block; padding: 14px 36px; background-color: #006198; color: #ffffff; font-size: 16px; text-decoration: none; border-radius: 5px;">
                            Login to Portal
                        </a>
                    </td>
                </tr>
                <tr>
                    <td style="font-size: 13px; color: #888888; line-height: 1.5;">
                        <p>For security reasons, please change your password after your first login.</p>
                        <p>If you did not expect this email, please contact the system administrator.</p>
                    </td>
                </tr>
                <tr>
                    <td style="padding-top: 20px;">
                        <hr style="border: none; border-top: 1px solid #eeeeee;">
                    </td>
                </tr>
                <tr>
                    <td align="center" style="padding-top: 15px; color: #aaaaaa; font-size: 12px;">
                        &copy; {{ date('Y') }} IIA Malawi. All rights reserved.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
