<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-primary leading-tight">
            {{ $isEdit ? 'Edit Listing' : 'Create Listing' }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            @if ($errors->any())
                <x-alert variant="danger" class="mb-4">{{ $errors->first() }}</x-alert>
            @endif
            @if ($isEdit && $listing->status === 'blocked')
                <x-alert variant="warning" class="mb-4">
                    This listing is blocked by admin. You can edit content, but only admin can unblock/publish it.
                </x-alert>
            @endif

            <form
                method="POST"
                action="{{ $isEdit ? route('vendor.listings.update', $listing) : route('vendor.listings.store') }}"
                class="space-y-6"
                x-data="{ listingType: '{{ old('type', $listing->type) }}' }"
                enctype="multipart/form-data"
            >
                @csrf
                @if ($isEdit)
                    @method('PUT')
                @endif

                <x-card title="Core Details">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <x-input-label for="title" value="Title" />
                            <x-text-input id="title" name="title" class="mt-1" :value="old('title', $listing->title)" required minlength="5" maxlength="120" />
                            <p class="mt-1 text-xs text-primary/60">5-120 characters. Use a specific title travelers will recognize.</p>
                        </div>
                        <div>
                            <x-input-label for="type" value="Type" />
                            <x-select-input id="type" name="type" class="mt-1" x-model="listingType">
                                <option value="tour" @selected(old('type', $listing->type) === 'tour')>Tour</option>
                                <option value="utility" @selected(old('type', $listing->type) === 'utility')>Utility</option>
                            </x-select-input>
                            <p class="mt-1 text-xs text-primary/60">Choose Tour for experiences/itineraries, or Utility for hotels, transport, attractions, and event spaces.</p>
                        </div>
                        <div x-cloak x-show="listingType === 'utility'">
                            <x-input-label for="subtype" value="Subtype (utility only)" />
                            <x-select-input id="subtype" name="subtype" class="mt-1" x-bind:disabled="listingType !== 'utility'">
                                <option value="">Select subtype</option>
                                @foreach ($utilitySubtypes as $value => $label)
                                    <option value="{{ $value }}" @selected(old('subtype', $listing->subtype) === $value)>{{ $label }}</option>
                                @endforeach
                            </x-select-input>
                            <p class="mt-1 text-xs text-primary/60">Required for utility listings. Pick the closest category so customers can filter correctly.</p>
                        </div>
                        <div class="md:col-span-2">
                            <x-input-label for="summary" value="Summary" />
                            <x-textarea-input id="summary" name="summary" class="mt-1" rows="2" maxlength="320">{{ old('summary', $listing->summary) }}</x-textarea-input>
                            <p class="mt-1 text-xs text-primary/60">Optional, up to 320 characters. Keep it short and benefit-focused.</p>
                        </div>
                        <div class="md:col-span-2">
                            <x-input-label for="description" value="Description" />
                            <x-textarea-input id="description" name="description" class="mt-1" rows="6" minlength="30" maxlength="5000">{{ old('description', $listing->description) }}</x-textarea-input>
                            <p class="mt-1 text-xs text-primary/60">30-5000 characters. Include what is included, timing, and what to expect.</p>
                        </div>
                    </div>
                </x-card>

                <x-card title="Pricing and Status">
                    <div class="grid gap-4 md:grid-cols-4">
                        <div>
                            <x-input-label for="price_from" value="Price From" />
                            <x-text-input id="price_from" name="price_from" type="number" step="0.01" class="mt-1" :value="old('price_from', $listing->price_from)" required />
                            <p class="mt-1 text-xs text-primary/60">Enter the lowest starting price in the selected currency.</p>
                        </div>
                        <div>
                            <x-input-label for="currency_code" value="Currency" />
                            <x-select-input id="currency_code" name="currency_code" class="mt-1">
                                @foreach ($currencies as $currency)
                                    <option value="{{ $currency->code }}" @selected(old('currency_code', $listing->currency_code) === $currency->code)>
                                        {{ $currency->code }} - {{ $currency->name }}
                                    </option>
                                @endforeach
                            </x-select-input>
                            <p class="mt-1 text-xs text-primary/60">Select the currency your base price is charged in.</p>
                        </div>
                        <div>
                            <x-input-label for="pricing_unit" value="Pricing Unit" />
                            <x-text-input id="pricing_unit" name="pricing_unit" class="mt-1" :value="old('pricing_unit', $listing->pricing_unit)" maxlength="80" />
                            <p class="mt-1 text-xs text-primary/60">Up to 80 characters. Example: per traveler, per night, per ticket.</p>
                        </div>
                        <div>
                            <x-input-label for="status" value="Status" />
                            <x-select-input id="status" name="status" class="mt-1">
                                @foreach (['draft', 'pending_review', 'published', 'paused'] as $status)
                                    <option value="{{ $status }}" @selected(old('status', $listing->status) === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                                @endforeach
                                @if ($isEdit && $listing->status === 'blocked')
                                    <option value="blocked" selected>Blocked (admin only)</option>
                                @endif
                            </x-select-input>
                            <p class="mt-1 text-xs text-primary/60">Draft/Paused are hidden. Published is visible. Pending review can be used before publishing.</p>
                        </div>
                    </div>
                </x-card>

                <x-card title="Location and Logistics">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <x-input-label for="city" value="City" />
                            <x-text-input id="city" name="city" class="mt-1" :value="old('city', $listing->city)" maxlength="120" />
                            <p class="mt-1 text-xs text-primary/60">Up to 120 characters.</p>
                        </div>
                        <div>
                            <x-input-label for="country" value="Country" />
                            @php
                                $selectedCountry = old('country', $listing->country);
                            @endphp
                            <x-select-input id="country" name="country" class="mt-1">
                                <option value="">Select country</option>
                                @foreach ($countries as $country)
                                    <option value="{{ $country }}" @selected($selectedCountry === $country)>{{ $country }}</option>
                                @endforeach
                                @if ($selectedCountry && ! in_array($selectedCountry, $countries, true))
                                    <option value="{{ $selectedCountry }}" selected>{{ $selectedCountry }} (current)</option>
                                @endif
                            </x-select-input>
                            <p class="mt-1 text-xs text-primary/60">Choose the listing country from the dropdown.</p>
                        </div>
                        <div class="md:col-span-2">
                            <x-input-label for="address" value="Address" />
                            <x-text-input id="address" name="address" class="mt-1" :value="old('address', $listing->address)" maxlength="255" />
                            <p class="mt-1 text-xs text-primary/60">Optional, up to 255 characters. Include landmarks when useful.</p>
                        </div>
                        <div>
                            <x-input-label for="duration_label" value="Duration Label" />
                            <x-text-input id="duration_label" name="duration_label" class="mt-1" :value="old('duration_label', $listing->duration_label)" maxlength="120" />
                            <p class="mt-1 text-xs text-primary/60">Optional, up to 120 characters. Example: 3 days / 2 nights.</p>
                        </div>
                        <div>
                            <x-input-label for="group_size_label" value="Group Size Label" />
                            <x-text-input id="group_size_label" name="group_size_label" class="mt-1" :value="old('group_size_label', $listing->group_size_label)" maxlength="120" />
                            <p class="mt-1 text-xs text-primary/60">Optional, up to 120 characters. Example: 1-6 travelers.</p>
                        </div>
                    </div>
                </x-card>

                <x-card title="Structured Content (One item per line)">
                    <p class="mb-3 text-xs text-primary/60">
                        Hint: one item per line, maximum 30 lines per field, and each line can be up to 180 characters.
                    </p>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <x-input-label for="highlights_text" value="Highlights" />
                            <x-textarea-input id="highlights_text" name="highlights_text" class="mt-1" rows="5" maxlength="5000" placeholder="Old-town heritage walk&#10;Sunset beach dinner&#10;Local market tasting">{{ old('highlights_text', collect($listing->highlights ?? [])->implode(PHP_EOL)) }}</x-textarea-input>
                            <p class="mt-1 text-xs text-primary/60">Enter key selling points, one per line. Keep each line short (up to 180 chars). Example: "Sunset beach dinner".</p>
                        </div>
                        <div>
                            <x-input-label for="amenities_text" value="Amenities" />
                            <x-textarea-input id="amenities_text" name="amenities_text" class="mt-1" rows="5" maxlength="5000" placeholder="Wi-Fi&#10;Air conditioning&#10;Private bathroom">{{ old('amenities_text', collect($listing->amenities ?? [])->implode(PHP_EOL)) }}</x-textarea-input>
                            <p class="mt-1 text-xs text-primary/60">List available amenities, one per line. Example: "Wi-Fi" or "Airport pickup".</p>
                        </div>
                        <div>
                            <x-input-label for="itinerary_text" value="Itinerary Steps" />
                            <x-textarea-input id="itinerary_text" name="itinerary_text" class="mt-1" rows="5" maxlength="8000" placeholder="08:00 - Hotel pickup and briefing&#10;10:30 - Guided city tour&#10;13:00 - Lunch break">{{ old('itinerary_text', collect($listing->itinerary ?? [])->implode(PHP_EOL)) }}</x-textarea-input>
                            <p class="mt-1 text-xs text-primary/60">Enter itinerary in sequence, one step per line. Start each line with time or phase (for example: "08:00 - Hotel pickup").</p>
                        </div>
                        <div>
                            <x-input-label for="languages_text" value="Languages" />
                            <x-textarea-input id="languages_text" name="languages_text" class="mt-1" rows="5" maxlength="1500" placeholder="English&#10;French">{{ old('languages_text', collect($listing->languages ?? [])->implode(PHP_EOL)) }}</x-textarea-input>
                            <p class="mt-1 text-xs text-primary/60">One language per line. Example: English, French, Swahili.</p>
                        </div>
                        <div>
                            <x-input-label for="inclusions_text" value="Inclusions" />
                            <x-textarea-input id="inclusions_text" name="inclusions_text" class="mt-1" rows="5" maxlength="5000" placeholder="Licensed guide&#10;Entrance tickets&#10;Lunch">{{ old('inclusions_text', collect($listing->inclusions ?? [])->implode(PHP_EOL)) }}</x-textarea-input>
                            <p class="mt-1 text-xs text-primary/60">List what the customer gets, one item per line.</p>
                        </div>
                        <div>
                            <x-input-label for="exclusions_text" value="Exclusions" />
                            <x-textarea-input id="exclusions_text" name="exclusions_text" class="mt-1" rows="5" maxlength="5000" placeholder="Flights&#10;Travel insurance&#10;Personal shopping">{{ old('exclusions_text', collect($listing->exclusions ?? [])->implode(PHP_EOL)) }}</x-textarea-input>
                            <p class="mt-1 text-xs text-primary/60">List what is not included, one item per line, to avoid booking confusion.</p>
                        </div>
                    </div>
                </x-card>

                <x-card title="Policies and Gallery">
                    <div class="grid gap-4">
                        <div>
                            <x-input-label for="booking_rules" value="Booking Rules" />
                            <x-textarea-input id="booking_rules" name="booking_rules" class="mt-1" rows="3" maxlength="2000">{{ old('booking_rules', $listing->booking_rules) }}</x-textarea-input>
                            <p class="mt-1 text-xs text-primary/60">Optional, up to 2000 characters. Include check-in, age, or ID requirements.</p>
                        </div>
                        <div>
                            <x-input-label for="cancellation_policy" value="Cancellation Policy" />
                            <x-textarea-input id="cancellation_policy" name="cancellation_policy" class="mt-1" rows="3" maxlength="2000">{{ old('cancellation_policy', $listing->cancellation_policy) }}</x-textarea-input>
                            <p class="mt-1 text-xs text-primary/60">Optional, up to 2000 characters. Example: full refund 48 hours before start.</p>
                        </div>
                        <div>
                            <x-input-label for="images" value="Listing Images" />
                            <input
                                id="images"
                                name="images[]"
                                type="file"
                                class="fc-input mt-1"
                                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                multiple
                            />
                            <p class="mt-1 text-xs text-primary/60">
                                Upload up to 12 images. Accepted types: JPG, JPEG, PNG, WEBP. Minimum size: 800x600 pixels. Maximum file size: 5MB each.
                            </p>
                            @if ($isEdit)
                                <p class="mt-1 text-xs text-primary/60">New uploads are added to your current gallery. Use the remove checkboxes below to delete specific images.</p>
                            @endif
                        </div>

                        @if ($listing->media->isNotEmpty())
                            <div>
                                <p class="text-sm font-medium text-primary">Current Gallery</p>
                                <p class="mt-1 text-xs text-primary/60">Choose one image as cover and check any images you want to remove, then click Update Listing.</p>
                                <div class="mt-2 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                    @foreach ($listing->media as $media)
                                        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white">
                                            <img src="{{ $media->url }}" alt="{{ $media->alt_text ?? $listing->title }}" class="h-32 w-full object-cover" />
                                            <div class="flex items-center justify-between px-3 py-2">
                                                <label class="inline-flex items-center gap-2 text-xs text-primary/80">
                                                    <input type="checkbox" name="remove_media_ids[]" value="{{ $media->id }}" class="fc-checkbox">
                                                    Remove
                                                </label>
                                                <label class="inline-flex items-center gap-2 text-xs text-primary/80">
                                                    <input
                                                        type="radio"
                                                        name="cover_media_id"
                                                        value="{{ $media->id }}"
                                                        class="fc-radio"
                                                        @checked((int) old('cover_media_id', $listing->media->firstWhere('is_cover', true)?->id) === $media->id)
                                                    >
                                                    Cover
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </x-card>

                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('vendor.listings.index') }}" class="fc-btn fc-btn-outline">Cancel</a>
                    <x-primary-button>{{ $isEdit ? 'Update Listing' : 'Create Listing' }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
