<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your One-Time Password</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, Helvetica, sans-serif; background-color: #f4f4f4;">
    <table role="presentation" style="width: 100%; border-collapse: collapse;">
        <tr>
            <td align="center" style="padding: 40px 0;">
                <table role="presentation" style="width: 600px; border-collapse: collapse; background-color: #ffffff; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="padding: 40px 30px; text-align: center; background-color: #0066cc;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 24px; font-weight: bold;">{{ config('app.name') }}</h1>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="margin: 0 0 20px 0; color: #333333; font-size: 20px;">Your One-Time Password (OTP)</h2>

                            <p style="margin: 0 0 20px 0; color: #666666; font-size: 16px; line-height: 24px;">
                                Hello,
                            </p>

                            <p style="margin: 0 0 30px 0; color: #666666; font-size: 16px; line-height: 24px;">
                                Here is your OTP code to proceed with your verification:
                            </p>

                            <!-- OTP Code Box -->
                            <table role="presentation" style="width: 100%; border-collapse: collapse; margin-bottom: 30px;">
                                <tr>
                                    <td align="center" style="padding: 20px; background-color: #f8f9fa; border: 2px dashed #0066cc; border-radius: 8px;">
                                        <span style="font-size: 32px; font-weight: bold; color: #0066cc; letter-spacing: 8px; font-family: 'Courier New', monospace;">{{ $otp }}</span>
                                    </td>
                                </tr>
                            </table>

                            <!-- Important Info -->
                            <table role="presentation" style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                                <tr>
                                    <td style="padding: 15px; background-color: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px;">
                                        <p style="margin: 0; color: #856404; font-size: 14px; line-height: 20px;">
                                            <strong>Important:</strong> This code will expire in <strong>{{ config('appointment.otp.expiry_minutes', 5) }} minutes</strong>.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Security Warning -->
                            <table role="presentation" style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                                <tr>
                                    <td style="padding: 15px; background-color: #f8d7da; border-left: 4px solid #dc3545; border-radius: 4px;">
                                        <p style="margin: 0 0 8px 0; color: #721c24; font-size: 14px; line-height: 20px;">
                                            <strong>Security Notice:</strong>
                                        </p>
                                        <ul style="margin: 0; padding-left: 20px; color: #721c24; font-size: 13px; line-height: 18px;">
                                            <li>Never share this code with anyone</li>
                                            <li>{{ config('app.name') }} will never ask for your OTP</li>
                                            <li>If you didn't request this code, please ignore this email</li>
                                        </ul>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 30px; text-align: center; background-color: #f8f9fa; border-top: 1px solid #dee2e6;">
                            <p style="margin: 0 0 10px 0; color: #666666; font-size: 14px;">
                                Thank you for using {{ config('app.name') }}
                            </p>
                            <p style="margin: 0; color: #999999; font-size: 12px;">
                                This is an automated message, please do not reply to this email.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
