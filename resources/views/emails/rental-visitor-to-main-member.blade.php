<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('messages.visitors_rental_email_subject', ['house' => $entry->houseUnit?->label() ?? '—']) }}</title>
</head>
<body style="margin:0;padding:0;background-color:#ECEAE1;font-family:Arial,Helvetica,sans-serif;color:#0F141E;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color:#ECEAE1;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:600px;background-color:#ffffff;border:1px solid #E6EBF4;border-radius:16px;overflow:hidden;">
                    <tr>
                        <td style="background-color:#080D21;padding:24px 28px;">
                            <p style="margin:0;font-size:12px;letter-spacing:0.18em;text-transform:uppercase;color:#E6C280;">{{ __('messages.brand_name') }}</p>
                            <h1 style="margin:8px 0 0;font-size:22px;line-height:1.3;color:#E6EBF4;">{{ __('messages.visitors_rental_email_heading') }}</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px;">
                            <p style="margin:0 0 16px;font-size:16px;line-height:1.6;color:#0F141E;">
                                {{ __('messages.visitors_rental_email_greeting', ['name' => $mainMember->fullName()]) }}
                            </p>
                            <p style="margin:0 0 20px;font-size:15px;line-height:1.6;color:#0F141E;">
                                {{ __('messages.visitors_rental_email_intro', [
                                    'rental' => $rentalMember->fullName(),
                                    'house' => $entry->houseUnit?->label() ?? '—',
                                ]) }}
                            </p>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:0 0 20px;background-color:#E6EBF4;border-radius:12px;">
                                <tr>
                                    <td style="padding:18px 20px;">
                                        <p style="margin:0 0 14px;font-size:13px;font-weight:bold;text-transform:uppercase;letter-spacing:0.08em;color:#080D21;">{{ __('messages.visitors_rental_email_details_heading') }}</p>

                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="font-size:14px;line-height:1.6;color:#0F141E;">
                                            <tr>
                                                <td style="padding:6px 0;color:#0F141E/70;width:40%;vertical-align:top;">{{ __('messages.visitors_entry_time') }}</td>
                                                <td style="padding:6px 0;font-weight:600;">{{ $entry->entry_at?->format('d M Y, h:i A') }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:6px 0;color:#0F141E/70;vertical-align:top;">{{ __('messages.visitors_name') }}</td>
                                                <td style="padding:6px 0;font-weight:600;">{{ $entry->visitor_name }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:6px 0;color:#0F141E/70;vertical-align:top;">{{ __('messages.visitors_contact') }}</td>
                                                <td style="padding:6px 0;font-weight:600;">{{ $entry->visitor_contact }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:6px 0;color:#0F141E/70;vertical-align:top;">{{ __('messages.visitors_party_heading') }}</td>
                                                <td style="padding:6px 0;font-weight:600;">
                                                    {{ __('messages.visitors_party_summary', [
                                                        'total' => $entry->party_size,
                                                        'male' => $entry->male_count,
                                                        'female' => $entry->female_count,
                                                        'children' => $entry->children_count,
                                                    ]) }}
                                                </td>
                                            </tr>
                                            @if ($entry->vehicle_number)
                                            <tr>
                                                <td style="padding:6px 0;color:#0F141E/70;vertical-align:top;">{{ __('messages.visitors_vehicle') }}</td>
                                                <td style="padding:6px 0;font-weight:600;">{{ $entry->vehicle_number }}</td>
                                            </tr>
                                            @endif
                                            @if ($entry->purpose)
                                            <tr>
                                                <td style="padding:6px 0;color:#0F141E/70;vertical-align:top;">{{ __('messages.visitors_purpose') }}</td>
                                                <td style="padding:6px 0;font-weight:600;">{{ $entry->purpose }}</td>
                                            </tr>
                                            @endif
                                            @if ($entry->notes)
                                            <tr>
                                                <td style="padding:6px 0;color:#0F141E/70;vertical-align:top;">{{ __('messages.visitors_notes') }}</td>
                                                <td style="padding:6px 0;font-weight:600;">{{ $entry->notes }}</td>
                                            </tr>
                                            @endif
                                            <tr>
                                                <td style="padding:6px 0;color:#0F141E/70;vertical-align:top;">{{ __('messages.visitors_logged_by') }}</td>
                                                <td style="padding:6px 0;font-weight:600;">{{ $entry->loggedBy?->fullName() ?? '—' }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            @if ($entry->photoUrl())
                            <p style="margin:0 0 8px;font-size:13px;font-weight:bold;text-transform:uppercase;letter-spacing:0.08em;color:#080D21;">{{ __('messages.visitors_photo') }}</p>
                            <p style="margin:0 0 16px;">
                                <a href="{{ $entry->photoUrl() }}" style="color:#AB1E23;font-weight:600;text-decoration:none;">{{ __('messages.visitors_view_document') }}</a>
                            </p>
                            <p style="margin:0 0 20px;">
                                <img src="{{ $entry->photoUrl() }}" alt="{{ $entry->visitor_name }}" style="max-width:100%;height:auto;border-radius:12px;border:1px solid #E6EBF4;">
                            </p>
                            @endif

                            @if ($entry->idProofUrl())
                            <p style="margin:0 0 8px;font-size:13px;font-weight:bold;text-transform:uppercase;letter-spacing:0.08em;color:#080D21;">{{ __('messages.visitors_id_proof') }}</p>
                            <p style="margin:0 0 20px;">
                                <a href="{{ $entry->idProofUrl() }}" style="color:#AB1E23;font-weight:600;text-decoration:none;">{{ __('messages.visitors_view_document') }}</a>
                            </p>
                            @endif

                            <p style="margin:0;font-size:13px;line-height:1.6;color:#0F141E/80;">
                                {{ __('messages.visitors_rental_email_footer') }}
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
