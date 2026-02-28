<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-primary leading-tight">Admin • User Management</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <x-alert variant="success">{{ session('status') }}</x-alert>
            @endif

            <x-card title="User Filters">
                <form method="GET" class="grid gap-3 md:grid-cols-4">
                    <x-text-input name="q" :value="request('q')" placeholder="Search by name or email" />
                    <x-select-input name="role">
                        <option value="">All roles</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role }}" @selected(request('role') === $role)>{{ str_replace('_', ' ', $role) }}</option>
                        @endforeach
                    </x-select-input>
                    <x-select-input name="blocked">
                        <option value="">All account states</option>
                        <option value="0" @selected(request('blocked') === '0')>Active</option>
                        <option value="1" @selected(request('blocked') === '1')>Blocked</option>
                    </x-select-input>
                    <x-button type="submit" variant="secondary">Apply Filters</x-button>
                </form>
            </x-card>

            <div class="grid gap-4 md:grid-cols-4">
                @foreach ($roleCounts as $role => $count)
                    <x-stat-card :label="ucfirst(str_replace('_', ' ', (string) $role))" :value="$count" />
                @endforeach
            </div>

            <x-card title="All Users">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-left text-primary/70">
                                <th class="py-2 pe-4">Name</th>
                                <th class="py-2 pe-4">Email</th>
                                <th class="py-2 pe-4">Role</th>
                                <th class="py-2 pe-4">Account</th>
                                <th class="py-2 pe-4">Created</th>
                                <th class="py-2 pe-4">Reset Password</th>
                                <th class="py-2 pe-4">Block / Unblock</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($users as $user)
                                <tr class="border-b border-slate-100 align-top">
                                    <td class="py-3 pe-4 text-primary">{{ $user->name }}</td>
                                    <td class="py-3 pe-4 text-primary/75">{{ $user->email }}</td>
                                    <td class="py-3 pe-4 text-primary/75 capitalize">{{ str_replace('_', ' ', (string) $user->user_role) }}</td>
                                    <td class="py-3 pe-4">
                                        <x-badge :variant="$user->is_blocked ? 'secondary' : 'primary'">
                                            {{ $user->is_blocked ? 'Blocked' : 'Active' }}
                                        </x-badge>
                                    </td>
                                    <td class="py-3 pe-4 text-primary/70">{{ $user->created_at->toDateString() }}</td>
                                    <td class="py-3 pe-4">
                                        <form method="POST" action="{{ route('admin.users.reset-password', $user) }}" class="flex items-center gap-2">
                                            @csrf
                                            <x-text-input name="new_password" placeholder="Optional custom password" class="w-52 text-xs" />
                                            <x-button type="submit" variant="outline" class="text-[10px]">Reset</x-button>
                                        </form>
                                    </td>
                                    <td class="py-3 pe-4">
                                        <form method="POST" action="{{ route('admin.users.block', $user) }}">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="is_blocked" value="{{ $user->is_blocked ? 0 : 1 }}">
                                            <x-button
                                                type="submit"
                                                :variant="$user->is_blocked ? 'secondary' : 'danger'"
                                                class="text-[10px]"
                                                :disabled="auth()->id() === $user->id && ! $user->is_blocked"
                                            >
                                                {{ $user->is_blocked ? 'Unblock' : 'Block' }}
                                            </x-button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-6 text-center text-primary/70">No users found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-6">{{ $users->links() }}</div>
            </x-card>
        </div>
    </div>
</x-app-layout>
