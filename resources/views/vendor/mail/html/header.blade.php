@props(['url'])

@php
    $appName = config('app.name', 'GH Tourist Hub');
    $providedUrl = rtrim((string) $url, '/');
    $configuredBaseUrl = rtrim((string) config('app.url', url('/')), '/');
    $homeUrl = $providedUrl !== '' ? $providedUrl : $configuredBaseUrl;
    $configuredLogoUrl = trim((string) config('mail.logo_url', ''));
    $logoUrl = $configuredLogoUrl !== '' ? $configuredLogoUrl : $homeUrl.'/images/logo/logo.png';
    $logoSrc = $logoUrl;

    if (str_starts_with($logoUrl, '/')) {
        $logoUrl = $homeUrl.$logoUrl;
        $logoSrc = $logoUrl;
    }

    // In local dev/MailHog, inline the logo so previews don't depend on an active web server.
    $logoPath = public_path('images/logo/logo.png');
    if (app()->environment('local') && is_file($logoPath)) {
        $extension = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION) ?: 'png');
        $mime = $extension === 'jpg' ? 'jpeg' : $extension;
        $inlineLogo = base64_encode((string) file_get_contents($logoPath));
        $logoSrc = 'data:image/'.$mime.';base64,'.$inlineLogo;
    }
@endphp

<tr>
<td class="header">
<a href="{{ $homeUrl }}" class="brand-link" style="display: inline-block;">
<img src="{{ $logoSrc }}" class="logo" alt="{{ $appName }} logo">
</a>
<p class="brand-name">{{ $appName }}</p>
<p class="brand-tagline">Discover. Book. Travel confidently.</p>
</td>
</tr>
