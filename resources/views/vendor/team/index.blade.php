<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-primary leading-tight">Team Management</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <x-alert variant="success">{{ session('status') }}</x-alert>
            @endif

            @if ($errors->any())
                <x-alert variant="danger">{{ $errors->first() }}</x-alert>
            @endif

            <x-card title="Create Staff Account">
                <p class="mb-4 text-sm text-primary/70">
                    Only the vendor owner can create staff users for {{ $tenant->name }}.
                </p>
                <form method="POST" action="{{ route('vendor.team.store') }}" class="grid gap-4 md:grid-cols-3">
                    @csrf
                    <div>
                        <x-input-label for="name" value="Full Name" />
                        <x-text-input id="name" name="name" class="mt-1" required />
                    </div>
                    <div>
                        <x-input-label for="email" value="Email" />
                        <x-text-input id="email" type="email" name="email" class="mt-1" required />
                    </div>
                    <div>
                        <x-input-label for="password" value="Password (optional)" />
                        <x-text-input id="password" name="password" class="mt-1" />
                    </div>
                    <div class="md:col-span-3">
                        <x-button type="submit" variant="secondary">Create Staff User</x-button>
                    </div>
                </form>
            </x-card>

            <x-card title="Current Staff Members">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-left text-primary/70">
                                <th class="py-2 pe-4">Name</th>
                                <th class="py-2 pe-4">Email</th>
                                <th class="py-2 pe-4">Role</th>
                                <th class="py-2 pe-4">Status</th>
                                <th class="py-2 pe-4"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($staffMembers as $member)
                                <tr class="border-b border-slate-100">
                                    <td class="py-3 pe-4 text-primary">{{ $member->user->name }}</td>
                                    <td class="py-3 pe-4 text-primary/75">{{ $member->user->email }}</td>
                                    <td class="py-3 pe-4 text-primary/75 capitalize">{{ str_replace('_', ' ', $member->role) }}</td>
                                    <td class="py-3 pe-4">
                                        <x-badge :variant="$member->is_active ? 'primary' : 'secondary'">
                                            {{ $member->is_active ? 'active' : 'inactive' }}
                                        </x-badge>
                                    </td>
                                    <td class="py-3 pe-4">
                                        @if ($member->is_active)
                                            <form method="POST" action="{{ route('vendor.team.deactivate', $member) }}">
                                                @csrf
                                                @method('PUT')
                                                <x-button type="submit" variant="outline" class="text-[10px]">Deactivate</x-button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-6 text-center text-primary/70">No staff members yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>
    </div>
</x-app-layout>
