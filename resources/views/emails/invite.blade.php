<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Invitation') }}</title>
</head>
<body style="font-family: sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <p>{{ __('You have been invited to join :app.', ['app' => config('app.name')]) }}</p>
    <p>{{ __('Click the link below to create your account. This link will expire.') }}</p>
    <p><a href="{{ $acceptUrl }}" style="display: inline-block; padding: 12px 24px; background: #2563eb; color: #fff; text-decoration: none; border-radius: 6px;">{{ __('Accept invitation') }}</a></p>
    <p style="color: #666; font-size: 14px;">{{ __('If you did not expect this invitation, you can ignore this email.') }}</p>
</body>
</html>
