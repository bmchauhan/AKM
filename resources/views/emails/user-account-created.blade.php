<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('messages.user_welcome_email_subject') }}</title>
</head>
<body style="margin:0;padding:0;background-color:#ECEAE1;font-family:Arial,Helvetica,sans-serif;color:#0F141E;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color:#ECEAE1;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:560px;background-color:#ffffff;border:1px solid #E6EBF4;border-radius:16px;overflow:hidden;">
                    <tr>
                        <td style="background-color:#080D21;padding:24px 28px;">
                            <p style="margin:0;font-size:12px;letter-spacing:0.18em;text-transform:uppercase;color:#E6C280;">{{ __('messages.brand_name') }}</p>
                            <h1 style="margin:8px 0 0;font-size:22px;line-height:1.3;color:#E6EBF4;">{{ __('messages.user_welcome_email_heading') }}</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px;">
                            <p style="margin:0 0 16px;font-size:16px;line-height:1.6;color:#0F141E;">
                                {{ __('messages.user_welcome_email_greeting', ['name' => $user->fullName()]) }}
                            </p>
                            <p style="margin:0 0 16px;font-size:15px;line-height:1.6;color:#0F141E;">
                                {{ __('messages.user_welcome_email_intro') }}
                            </p>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:0 0 20px;background-color:#E6EBF4;border-radius:12px;">
                                <tr>
                                    <td style="padding:18px 20px;">
                                        <p style="margin:0 0 10px;font-size:13px;font-weight:bold;text-transform:uppercase;letter-spacing:0.08em;color:#080D21;">{{ __('messages.user_welcome_email_role_label') }}</p>
                                        <p style="margin:0 0 16px;font-size:15px;color:#0F141E;">{{ $user->roleLabelWithShortForm() }}</p>

                                        <p style="margin:0 0 10px;font-size:13px;font-weight:bold;text-transform:uppercase;letter-spacing:0.08em;color:#080D21;">{{ __('messages.user_welcome_email_username_label') }}</p>
                                        <p style="margin:0 0 16px;font-size:15px;color:#0F141E;">{{ $user->username }}</p>

                                        <p style="margin:0 0 10px;font-size:13px;font-weight:bold;text-transform:uppercase;letter-spacing:0.08em;color:#080D21;">{{ __('messages.user_welcome_email_password_label') }}</p>
                                        <p style="margin:0;font-size:15px;font-family:Consolas,Monaco,monospace;color:#AB1E23;">{{ $plainPassword }}</p>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0 0 20px;font-size:14px;line-height:1.6;color:#0F141E;">
                                {{ __('messages.user_welcome_email_login_hint') }}
                            </p>

                            <a href="{{ url(route('login')) }}" style="display:inline-block;background-color:#AB1E23;color:#E6EBF4;text-decoration:none;font-size:15px;font-weight:600;padding:12px 20px;border-radius:8px;">
                                {{ __('messages.user_welcome_email_login_button') }}
                            </a>

                            <p style="margin:24px 0 0;font-size:12px;line-height:1.6;color:#0F141E;">
                                {{ __('messages.user_welcome_email_security_note') }}
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
