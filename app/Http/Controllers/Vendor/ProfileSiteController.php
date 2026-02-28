<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\VendorProfile;
use Illuminate\Contracts\View\View;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfileSiteController extends Controller
{
    public function edit(): View
    {
        $tenant = request()->user()->primaryTenant();
        abort_unless($tenant, 403);

        $tenant->load('profile');

        return view('vendor.profile-site.edit', [
            'tenant' => $tenant,
            'profile' => $tenant->profile,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $tenant = $request->user()->primaryTenant();
        abort_unless($tenant, 403);

        $tenant->load('profile');
        $profile = $tenant->profile;

        $validated = $request->validate([
            'tenant_name' => ['required', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'max:120'],
            'support_email' => ['nullable', 'email', 'max:255'],
            'support_phone' => ['nullable', 'string', 'max:120'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'logo_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:min_width=200,min_height=200'],
            'banner_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096', 'dimensions:min_width=1200,min_height=400'],
            'founded_year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'about' => ['nullable', 'string', 'max:5000'],
        ], [
            'logo_image.mimes' => 'Logo must be JPG, JPEG, PNG, or WEBP.',
            'logo_image.max' => 'Logo must be 2MB or smaller.',
            'logo_image.dimensions' => 'Logo must be at least 200x200 pixels.',
            'banner_image.mimes' => 'Banner must be JPG, JPEG, PNG, or WEBP.',
            'banner_image.max' => 'Banner must be 4MB or smaller.',
            'banner_image.dimensions' => 'Banner must be at least 1200x400 pixels.',
        ]);

        $tenant->update([
            'name' => $validated['tenant_name'],
        ]);

        $tenant->profile()->updateOrCreate(
            ['tenant_id' => $tenant->id],
            [
                'city' => $validated['city'] ?? null,
                'country' => $validated['country'] ?? null,
                'support_email' => $validated['support_email'] ?? null,
                'support_phone' => $validated['support_phone'] ?? null,
                'website_url' => $validated['website_url'] ?? null,
                'logo_url' => $this->storeProfileImage($tenant->id, $profile, 'logo_url', $request->file('logo_image'), 'logo'),
                'banner_url' => $this->storeProfileImage($tenant->id, $profile, 'banner_url', $request->file('banner_image'), 'banner'),
                'founded_year' => $validated['founded_year'] ?? null,
                'about' => $validated['about'] ?? null,
            ]
        );

        return back()->with('status', 'Mini website profile updated.');
    }

    protected function storeProfileImage(
        int $tenantId,
        ?VendorProfile $profile,
        string $column,
        ?UploadedFile $uploadedFile,
        string $folder
    ): ?string {
        $existingUrl = $profile?->{$column};
        if (! $uploadedFile) {
            return $existingUrl;
        }

        $oldPath = $this->mediaUrlToPublicPath((string) $existingUrl);
        if ($oldPath) {
            Storage::disk('public')->delete($oldPath);
        }

        $storedPath = $uploadedFile->store("vendor-profiles/{$tenantId}/{$folder}", 'public');

        return '/storage/'.$storedPath;
    }

    protected function mediaUrlToPublicPath(string $url): ?string
    {
        $path = (string) parse_url($url, PHP_URL_PATH);
        if (str_starts_with($path, '/storage/')) {
            return Str::after($path, '/storage/');
        }

        return null;
    }
}
