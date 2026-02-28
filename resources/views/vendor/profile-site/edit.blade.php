<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-primary leading-tight">Vendor Mini Website Profile</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <x-alert variant="success" class="mb-4">{{ session('status') }}</x-alert>
            @endif
            @if ($errors->any())
                <x-alert variant="danger" class="mb-4">{{ $errors->first() }}</x-alert>
            @endif

            <form method="POST" action="{{ route('vendor.site-profile.update') }}" class="space-y-6" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <x-card title="Public Company Information">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <x-input-label for="tenant_name" value="Public Business Name" />
                            <x-text-input id="tenant_name" name="tenant_name" class="mt-1" :value="old('tenant_name', $tenant->name)" required maxlength="255" />
                            <p class="mt-1 text-xs text-primary/60">Required, up to 255 characters. This is the name shown on your public mini website.</p>
                        </div>
                        <div>
                            <x-input-label for="city" value="City" />
                            <x-text-input id="city" name="city" class="mt-1" :value="old('city', $profile?->city)" maxlength="120" placeholder="Accra" />
                            <p class="mt-1 text-xs text-primary/60">Optional, up to 120 characters. Enter the main city where you operate.</p>
                        </div>
                        <div>
                            <x-input-label for="country" value="Country" />
                            <x-text-input id="country" name="country" class="mt-1" :value="old('country', $profile?->country)" maxlength="120" placeholder="Ghana" />
                            <p class="mt-1 text-xs text-primary/60">Optional, up to 120 characters. Use full country name.</p>
                        </div>
                        <div>
                            <x-input-label for="support_email" value="Support Email" />
                            <x-text-input id="support_email" name="support_email" type="email" class="mt-1" :value="old('support_email', $profile?->support_email)" maxlength="255" placeholder="support@yourcompany.com" />
                            <p class="mt-1 text-xs text-primary/60">Optional, valid email format, up to 255 characters.</p>
                        </div>
                        <div>
                            <x-input-label for="support_phone" value="Support Phone" />
                            <x-text-input id="support_phone" name="support_phone" class="mt-1" :value="old('support_phone', $profile?->support_phone)" maxlength="120" placeholder="+233 20 000 0000" />
                            <p class="mt-1 text-xs text-primary/60">Optional, up to 120 characters. Include country code for easier contact.</p>
                        </div>
                        <div>
                            <x-input-label for="website_url" value="Website URL" />
                            <x-text-input id="website_url" name="website_url" class="mt-1" :value="old('website_url', $profile?->website_url)" maxlength="255" placeholder="https://example.com" />
                            <p class="mt-1 text-xs text-primary/60">Optional, must be a valid full URL including https:// (up to 255 characters).</p>
                        </div>
                        <div>
                            <x-input-label for="founded_year" value="Founded Year" />
                            <x-text-input id="founded_year" name="founded_year" type="number" class="mt-1" :value="old('founded_year', $profile?->founded_year)" min="1900" max="2100" />
                            <p class="mt-1 text-xs text-primary/60">Optional, numbers only. Allowed range: 1900 to 2100.</p>
                        </div>
                        <div class="md:col-span-2">
                            <x-input-label for="logo_image" value="Logo Image" />
                            <input
                                id="logo_image"
                                name="logo_image"
                                type="file"
                                class="fc-input mt-1"
                                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                            />
                            <p class="mt-1 text-xs text-primary/60">
                                Upload logo (JPG, JPEG, PNG, WEBP). Minimum 200x200px. Maximum 2MB.
                            </p>
                            @if ($profile?->logo_url)
                                <div class="mt-2 w-fit rounded-lg border border-slate-200 bg-white p-2">
                                    <img src="{{ $profile->logo_url }}" alt="{{ $tenant->name }} logo" class="h-16 w-16 object-cover" />
                                </div>
                            @endif
                        </div>
                        <div class="md:col-span-2">
                            <x-input-label for="banner_image" value="Banner Image" />
                            <input
                                id="banner_image"
                                name="banner_image"
                                type="file"
                                class="fc-input mt-1"
                                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                            />
                            <p class="mt-1 text-xs text-primary/60">
                                Upload banner (JPG, JPEG, PNG, WEBP). Minimum 1200x400px. Maximum 4MB.
                            </p>
                            @if ($profile?->banner_url)
                                <div class="mt-2 overflow-hidden rounded-lg border border-slate-200 bg-white">
                                    <img src="{{ $profile->banner_url }}" alt="{{ $tenant->name }} banner" class="h-24 w-full max-w-md object-cover" />
                                </div>
                            @endif
                        </div>
                        <div class="md:col-span-2">
                            <x-input-label for="about" value="About (used in your mini website)" />
                            <x-textarea-input id="about" name="about" class="mt-1" rows="7" maxlength="5000" placeholder="Tell customers what you offer, where you operate, and what makes your service unique.">{{ old('about', $profile?->about) }}</x-textarea-input>
                            <p class="mt-1 text-xs text-primary/60">Optional, up to 5000 characters. Include your specialties, destinations, and service style.</p>
                        </div>
                    </div>
                </x-card>

                <div class="flex items-center justify-between">
                    <a href="{{ route('marketplace.vendor', $tenant->slug) }}" class="fc-btn fc-btn-outline" target="_blank">Preview Mini Website</a>
                    <x-primary-button>Save Profile</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
