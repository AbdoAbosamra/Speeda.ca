<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Url;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

new class extends Component {
    #[Url(as: 'search', except: '')]
    public $search = '';

    #[Url(as: 'role', except: '')]
    public $role = '';

    #[Url(as: 'status', except: '')]
    public $status = '';

    public $showInactive = true;

    public $selectedUsers = [];
    public $selectAll = false;

    #[Url(as: 'sortField', except: 'created_at')]
    public $sortField = 'created_at';

    #[Url(as: 'sortDirection', except: 'desc')]
    public $sortDirection = 'desc';

    #[Url]
    public $page = 1;

    public $perPage = 15;

    public function updatedSearch() { $this->page = 1; }
    public function updatedRole() { $this->page = 1; }
    public function updatedStatus() { $this->page = 1; }
    public function updatedShowInactive() { $this->page = 1; }

    public function goToPage($pageNum) { $this->page = (int) $pageNum; }
    public function prevPage() { $this->page = max(1, $this->page - 1); }
    public function nextPage() { $this->page = $this->page + 1; }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedUsers = $this->getUsersProperty()->pluck('id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->selectedUsers = [];
        }
    }

    public function updatedSelectedUsers()
    {
        $this->selectAll = count($this->selectedUsers) === $this->getUsersProperty()->count() && $this->getUsersProperty()->count() > 0;
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        $this->page = 1;
    }

    public function toggleStatus($userId)
    {
        $user = User::findOrFail($userId);
        if ($user->id === auth()->id())
            return;

        $user->is_active = !$user->is_active;
        $user->save();

        session()->flash('message', 'Status updated successfully.');
    }

    public function deleteUser($userId)
    {
        $user = User::findOrFail($userId);
        if ($user->id === auth()->id() || $user->role === 'admin')
            return;

        $user->delete();
        session()->flash('message', 'User moved to trash.');
    }

    public function bulkActivate()
    {
        User::whereIn('id', $this->selectedUsers)->update(['is_active' => true]);
        $this->reset(['selectedUsers', 'selectAll']);
        session()->flash('message', 'Selected users activated.');
    }

    public function bulkDeactivate()
    {
        User::whereIn('id', $this->selectedUsers)
            ->where('id', '!=', auth()->id())
            ->update(['is_active' => false]);
        $this->reset(['selectedUsers', 'selectAll']);
        session()->flash('message', 'Selected users deactivated.');
    }

    public function bulkDelete()
    {
        User::whereIn('id', $this->selectedUsers)
            ->where('id', '!=', auth()->id())
            ->where('role', '!=', 'admin')
            ->delete();
        $this->reset(['selectedUsers', 'selectAll']);
        session()->flash('message', 'Selected users moved to trash.');
    }

    public function getUsersProperty()
    {
        $allowedSortFields = ['name', 'email', 'role', 'created_at', 'is_active'];
        $field = in_array($this->sortField, $allowedSortFields) ? $this->sortField : 'created_at';
        $dir = $this->sortDirection === 'asc' ? 'asc' : 'desc';

        return User::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->role, fn($query) => $query->where('role', $this->role))
            ->when($this->status === 'active', fn($query) => $query->where('is_active', true))
            ->when($this->status === 'inactive', fn($query) => $query->where('is_active', false))
            ->when(!$this->showInactive, fn($query) => $query->where('is_active', true))
            ->withCount(['reviews', 'comments', 'bookings'])
            ->orderBy($field, $dir)
            ->paginate($this->perPage, ['*'], 'page', $this->page);
    }

    public function with()
    {
        return [
            'users' => $this->getUsersProperty(),
            'stats' => [
                'total' => User::count(),
                'active' => User::where('is_active', true)->count(),
                'inactive' => User::where('is_active', false)->count(),
                'providers' => User::where('role', 'service_provider')->count(),
            ]
        ];
    }
};
?>

<div class="us-mgmt" x-data="{ bulkOpen: false }">
    @if (session()->has('message'))
        <div class="us-toast" x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" x-transition>
            <i class="fas fa-check-circle"></i>
            {{ session('message') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="us-header">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <h1 class="us-header-title">Users Management</h1>
                <p class="us-header-sub">Manage all users, their roles, and account status</p>
            </div>
            <a href="{{ route('admin.users.trash') }}" class="us-btn us-btn-ghost">
                <i class="fas fa-trash-alt"></i> Trash Bin
            </a>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="us-stats">
        <div class="us-stat">
            <div class="us-stat-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="us-stat-label">Total Users</div>
                        <div class="us-stat-value">{{ $stats['total'] }}</div>
                    </div>
                    <div class="us-stat-icon us-stat-icon-total">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="us-stat">
            <div class="us-stat-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="us-stat-label">Active</div>
                        <div class="us-stat-value us-stat-value-success">{{ $stats['active'] }}</div>
                    </div>
                    <div class="us-stat-icon us-stat-icon-active">
                        <i class="fas fa-user-check"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="us-stat">
            <div class="us-stat-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="us-stat-label">Inactive</div>
                        <div class="us-stat-value us-stat-value-warning">{{ $stats['inactive'] }}</div>
                    </div>
                    <div class="us-stat-icon us-stat-icon-inactive">
                        <i class="fas fa-user-slash"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="us-stat">
            <div class="us-stat-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="us-stat-label">Providers</div>
                        <div class="us-stat-value us-stat-value-info">{{ $stats['providers'] }}</div>
                    </div>
                    <div class="us-stat-icon us-stat-icon-providers">
                        <i class="fas fa-briefcase"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters & Bulk Actions --}}
    <div class="us-filters">
        <div class="row g-3 align-items-center">
            <div class="col-md-3">
                <div class="us-search-wrap">
                    <i class="fas fa-search us-search-icon"></i>
                    <input type="text" wire:model.live.debounce.300ms="search" class="us-search-input" placeholder="Search by name or email...">
                </div>
            </div>
            <div class="col-md-2">
                <select wire:model.live="role" class="us-select">
                    <option value="">All Roles</option>
                    <option value="client">Client</option>
                    <option value="service_provider">Provider</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div class="col-md-2">
                <select wire:model.live="status" class="us-select">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="us-toggle-label">
                    <input type="checkbox" class="us-toggle-input" wire:model.live="showInactive">
                    <span class="us-toggle-switch"></span>
                    <span class="us-toggle-text">Show Inactive</span>
                </label>
            </div>
            <div class="col-md-3 text-end">
                @if(count($selectedUsers) > 0)
                    <div class="d-inline-block" x-data="{ open: false }" @click.away="open = false">
                        <button class="us-btn us-btn-bulk" @click="open = !open">
                            <i class="fas fa-tasks"></i> Bulk ({{ count($selectedUsers) }})
                        </button>
                        <div x-show="open" x-transition class="us-dropdown" style="right: 0;">
                            <a class="us-dropdown-item" href="#" wire:click.prevent="bulkActivate">
                                <i class="fas fa-check-circle text-success"></i> Activate
                            </a>
                            <a class="us-dropdown-item" href="#" wire:click.prevent="bulkDeactivate">
                                <i class="fas fa-ban text-warning"></i> Deactivate
                            </a>
                            <div class="us-dropdown-divider"></div>
                            <a class="us-dropdown-item us-dropdown-item-danger" href="#" wire:click.prevent="bulkDelete">
                                <i class="fas fa-trash-alt"></i> Move to Trash
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Users Table --}}
    <div class="us-table-wrap">
        <div class="table-responsive">
            <table class="us-table" role="table" aria-label="Users list">
                <thead>
                    <tr>
                        <th class="us-th us-th-check">
                            <input type="checkbox" class="us-check" wire:model.live="selectAll" aria-label="Select all users">
                        </th>
                        <th class="us-th us-th-sort" wire:click="sortBy('name')" role="columnheader" aria-sort="{{ $sortField === 'name' ? ($sortDirection === 'asc' ? 'ascending' : 'descending') : 'none' }}" tabindex="0">
                            User
                            @if($sortField === 'name') <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i> @else <i class="fas fa-sort text-muted opacity-25"></i> @endif
                        </th>
                        <th class="us-th us-th-sort" wire:click="sortBy('role')" role="columnheader" aria-sort="{{ $sortField === 'role' ? ($sortDirection === 'asc' ? 'ascending' : 'descending') : 'none' }}" tabindex="0">
                            Role
                            @if($sortField === 'role') <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i> @else <i class="fas fa-sort text-muted opacity-25"></i> @endif
                        </th>
                        <th class="us-th">Activity (S/C/B)</th>
                        <th class="us-th us-th-sort" wire:click="sortBy('is_active')" role="columnheader" aria-sort="{{ $sortField === 'is_active' ? ($sortDirection === 'asc' ? 'ascending' : 'descending') : 'none' }}" tabindex="0">
                            Status
                            @if($sortField === 'is_active') <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i> @else <i class="fas fa-sort text-muted opacity-25"></i> @endif
                        </th>
                        <th class="us-th us-th-sort" wire:click="sortBy('created_at')" role="columnheader" aria-sort="{{ $sortField === 'created_at' ? ($sortDirection === 'asc' ? 'ascending' : 'descending') : 'none' }}" tabindex="0">
                            Created
                            @if($sortField === 'created_at') <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i> @else <i class="fas fa-sort text-muted opacity-25"></i> @endif
                        </th>
                        <th class="us-th us-th-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr wire:key="user-{{ $user->id }}" class="us-tr {{ !$user->is_active ? 'us-tr-inactive' : '' }}">
                            <td class="us-td us-td-check">
                                <input type="checkbox" class="us-check" wire:model.live="selectedUsers" value="{{ $user->id }}" aria-label="Select {{ $user->name }}">
                            </td>
                            <td class="us-td">
                                <div class="us-user-cell">
                                    <div class="us-avatar" style="background: {{ $user->is_active ? 'linear-gradient(135deg, #4f46e5, #6366f1)' : '#cbd5e1' }};" aria-hidden="true">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="us-user-name">{{ $user->name }}</div>
                                        <div class="us-user-email">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="us-td">
                                <span class="us-role us-role-{{ $user->role }}">
                                    {{ $user->role === 'service_provider' ? 'Provider' : ucfirst($user->role) }}
                                </span>
                            </td>
                            <td class="us-td">
                                <div class="us-activity">
                                    <span title="Reviews"><i class="fas fa-star us-activity-icon us-activity-icon-star"></i>{{ $user->reviews_count }}</span>
                                    <span title="Comments"><i class="fas fa-comment us-activity-icon us-activity-icon-comment"></i>{{ $user->comments_count }}</span>
                                    <span title="Bookings"><i class="fas fa-calendar-check us-activity-icon us-activity-icon-booking"></i>{{ $user->bookings_count }}</span>
                                </div>
                            </td>
                            <td class="us-td">
                                <label class="us-toggle-label us-toggle-sm" aria-label="Toggle status for {{ $user->name }}">
                                    <input type="checkbox" class="us-toggle-input" 
                                           wire:click="toggleStatus({{ $user->id }})" 
                                           wire:confirm="Are you sure you want to change this user's account status?"
                                           {{ $user->is_active ? 'checked' : '' }} 
                                           {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                                    <span class="us-toggle-switch"></span>
                                    <span class="us-toggle-text us-toggle-text-status {{ $user->is_active ? 'text-success' : 'text-muted' }}">
                                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </label>
                            </td>
                            <td class="us-td us-td-date">
                                <span>{{ $user->created_at->format('M d, Y') }}</span>
                                <span class="us-date-time">{{ $user->created_at->format('H:i') }}</span>
                            </td>
                            <td class="us-td us-td-actions">
                                <div class="us-action-group">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="us-action-btn" title="Edit {{ $user->name }}" aria-label="Edit {{ $user->name }}">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    @if($user->id !== auth()->id() && $user->role !== 'admin')
                                        <button wire:click="deleteUser({{ $user->id }})" wire:confirm="Move this user to trash?" class="us-action-btn us-action-btn-danger" title="Delete {{ $user->name }}" aria-label="Delete {{ $user->name }}">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="us-empty">
                                <i class="fas fa-search us-empty-icon"></i>
                                <h4>No users found</h4>
                                <p>No users match your current search or filter criteria.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <!-- PAGINATION -->
        <div class="us-pagination-footer mt-4 p-4 bg-white rounded-4 border">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="text-muted small">
                    Showing <strong>{{ $users->firstItem() }}</strong> to <strong>{{ $users->lastItem() }}</strong> of <strong>{{ $users->total() }}</strong> users
                </div>
                
                @if($users->hasPages())
                    <div class="us-pagination-pages d-flex gap-2">
                        {{-- Previous Page --}}
                        @if ($users->onFirstPage())
                            <span class="us-pagination-btn disabled">← Prev</span>
                        @else
                            <a href="{{ $users->previousPageUrl() }}" class="us-pagination-btn">← Prev</a>
                        @endif

                        {{-- Page Numbers --}}
                        @foreach ($users->getUrlRange(max(1, $users->currentPage() - 2), min($users->lastPage(), $users->currentPage() + 2)) as $page => $url)
                            <a href="{{ $url }}" 
                               class="us-pagination-btn {{ $page == $users->currentPage() ? 'active' : '' }}">
                                {{ $page }}
                            </a>
                        @endforeach

                        {{-- Next Page --}}
                        @if ($users->hasMorePages())
                            <a href="{{ $users->nextPageUrl() }}" class="us-pagination-btn">Next →</a>
                        @else
                            <span class="us-pagination-btn disabled">Next →</span>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
    /* Pagination Styles */
    .us-pagination-btn {
        padding: 0.5rem 1rem;
        border-radius: 0.75rem;
        border: 1px solid #eef2f6;
        background: white;
        color: #475569;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }
    .us-pagination-btn:hover:not(.disabled) {
        background: #f8fafc;
        border-color: #4f46e5;
        color: #4f46e5;
        transform: translateY(-1px);
    }
    .us-pagination-btn.active {
        background: #4f46e5;
        color: white;
        border-color: #4f46e5;
    }
    .us-pagination-btn.disabled {
        opacity: 0.5;
        cursor: not-allowed;
        background: #f8fafc;
    }

    /* ===== Users Management — Premium Design ===== */
    .us-mgmt { padding-top: 0; }

    /* Toast */
    .us-toast {
        position: fixed; top: 1.5rem; right: 1.5rem; z-index: 9999;
        background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0;
        padding: 0.85rem 1.5rem; border-radius: 1rem; font-weight: 600;
        display: flex; align-items: center; gap: 0.75rem;
        box-shadow: 0 12px 24px rgba(0,0,0,0.08);
    }

    /* Header */
    .us-header {
        padding: 1.5rem 2rem;
        background: linear-gradient(135deg, rgba(255,255,255,0.95), rgba(249,250,252,0.9));
        border-radius: 1.25rem; border: 1px solid #eef2f6;
        margin-bottom: 1.5rem;
    }
    .us-header-title { font-size: 1.75rem; font-weight: 800; color: #0f172a; letter-spacing: -0.02em; margin-bottom: 0.25rem; }
    .us-header-sub { font-size: 0.95rem; color: #475569; margin-bottom: 0; }

    /* Buttons */
    .us-btn {
        display: inline-flex; align-items: center; gap: 0.45rem;
        padding: 0.55rem 1.1rem; border-radius: 0.75rem;
        font-size: 0.85rem; font-weight: 600; text-decoration: none;
        transition: all 0.2s; border: none; cursor: pointer; line-height: 1;
    }
    .us-btn:hover { transform: translateY(-1px); }
    .us-btn-ghost { background: #f8fafc; color: #475569; border: 1px solid #eef2f6; }
    .us-btn-ghost:hover { background: #eef2ff; color: #4f46e5; border-color: rgba(79,70,229,0.2); }
    .us-btn-bulk {
        background: linear-gradient(135deg, #4f46e5, #6366f1);
        color: white; box-shadow: 0 4px 12px rgba(79,70,229,0.2);
    }
    .us-btn-bulk:hover { box-shadow: 0 6px 16px rgba(79,70,229,0.3); color: white; }

    /* Stats */
    .us-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
    .us-stat {
        background: white; border-radius: 1.25rem; border: 1px solid #eef2f6;
        padding: 1.25rem 1.5rem; transition: all 0.3s ease;
    }
    .us-stat:hover { box-shadow: 0 12px 24px -8px rgba(0,0,0,0.06); transform: translateY(-2px); }
    .us-stat-label { font-size: 0.8rem; color: #475569; text-transform: uppercase; letter-spacing: 0.03em; font-weight: 600; margin-bottom: 0.25rem; }
    .us-stat-value { font-size: 1.75rem; font-weight: 800; color: #0f172a; line-height: 1.1; }
    .us-stat-value-success { color: #059669; }
    .us-stat-value-warning { color: #d97706; }
    .us-stat-value-info { color: #2563eb; }
    .us-stat-icon {
        width: 44px; height: 44px; display: flex; align-items: center; justify-content: center;
        border-radius: 12px; font-size: 1.1rem;
    }
    .us-stat-icon-total { background: #eef2ff; color: #4f46e5; }
    .us-stat-icon-active { background: #ecfdf5; color: #059669; }
    .us-stat-icon-inactive { background: #fefce8; color: #d97706; }
    .us-stat-icon-providers { background: #eff6ff; color: #2563eb; }

    /* Filters */
    .us-filters {
        background: white; border-radius: 1.25rem; border: 1px solid #eef2f6;
        padding: 1rem 1.5rem; margin-bottom: 1.5rem; position: relative;
    }
    .us-search-wrap { position: relative; }
    .us-search-icon { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 0.9rem; }
    .us-search-input {
        width: 100%; padding: 0.6rem 1rem 0.6rem 2.5rem;
        border: 1px solid #eef2f6; border-radius: 0.75rem; font-size: 0.9rem;
        background: #f8fafc; transition: all 0.2s;
    }
    .us-search-input:focus { outline: none; border-color: #4f46e5; background: white; box-shadow: 0 0 0 3px rgba(79,70,229,0.1); }
    .us-select {
        width: 100%; padding: 0.6rem 2rem 0.6rem 1rem;
        border: 1px solid #eef2f6; border-radius: 0.75rem; font-size: 0.85rem;
        background: #f8fafc url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23475569' d='M6 8L1 3h10z'/%3E%3C/svg%3E") no-repeat right 0.75rem center;
        appearance: none; cursor: pointer; transition: all 0.2s;
    }
    .us-select:focus { outline: none; border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79,70,229,0.1); }

    /* Toggle */
    .us-toggle-label { display: inline-flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.85rem; }
    .us-toggle-input { display: none; }
    .us-toggle-switch {
        width: 36px; height: 20px; background: #cbd5e1; border-radius: 10px;
        position: relative; transition: background 0.2s; flex-shrink: 0;
    }
    .us-toggle-switch::before {
        content: ''; position: absolute; top: 2px; left: 2px;
        width: 16px; height: 16px; background: white; border-radius: 50%;
        transition: transform 0.2s;
    }
    .us-toggle-input:checked + .us-toggle-switch { background: #4f46e5; }
    .us-toggle-input:checked + .us-toggle-switch::before { transform: translateX(16px); }
    .us-toggle-input:disabled + .us-toggle-switch { opacity: 0.5; cursor: not-allowed; }
    .us-toggle-sm .us-toggle-switch { width: 30px; height: 18px; }
    .us-toggle-sm .us-toggle-switch::before { width: 14px; height: 14px; }
    .us-toggle-sm .us-toggle-input:checked + .us-toggle-switch::before { transform: translateX(12px); }
    .us-toggle-text { font-weight: 500; }
    .us-toggle-text-status { font-size: 0.82rem; }

    /* Dropdown */
    .us-dropdown {
        position: absolute; top: calc(100% + 8px); right: 0; min-width: 200px;
        background: white; border: 1px solid #eef2f6;
        border-radius: 1rem; box-shadow: 0 20px 40px -12px rgba(0,0,0,0.12);
        padding: 0.5rem; z-index: 50;
    }
    .us-dropdown-item {
        display: flex; align-items: center; gap: 0.75rem;
        padding: 0.6rem 0.85rem; border-radius: 0.65rem;
        font-size: 0.85rem; font-weight: 500; color: #0f172a;
        text-decoration: none; transition: background 0.15s; cursor: pointer;
    }
    .us-dropdown-item:hover { background: #f8fafc; }
    .us-dropdown-item-danger:hover { background: #fef2f2; }
    .us-dropdown-item-danger i { color: #dc2626; }
    .us-dropdown-divider { height: 1px; background: #eef2f6; margin: 0.35rem 0; }

    /* Table */
    .us-table-wrap {
        background: white; border-radius: 1.25rem; border: 1px solid #eef2f6; overflow: hidden;
    }
    .us-table { width: 100%; border-collapse: collapse; }
    .us-th {
        padding: 1rem 0.75rem; font-size: 0.8rem; font-weight: 600;
        color: #475569; text-transform: uppercase; letter-spacing: 0.04em;
        background: #f8fafc; border-bottom: 1px solid #eef2f6; text-align: left;
        white-space: nowrap;
    }
    .us-th-check { width: 48px; padding-left: 1.5rem; }
    .us-th-sort { cursor: pointer; user-select: none; }
    .us-th-sort:hover { color: #4f46e5; background: #f1f5f9; }
    .us-th-sort i { margin-left: 0.35rem; font-size: 0.75rem; }
    .us-th-actions { text-align: right; padding-right: 1.5rem; }

    .us-td { padding: 0.85rem 0.75rem; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
    .us-td-check { padding-left: 1.5rem; }
    .us-td-actions { text-align: right; padding-right: 1.5rem; }
    .us-td-date { white-space: nowrap; }
    .us-date-time { display: block; font-size: 0.75rem; color: #94a3b8; line-height: 1; }

    .us-tr { transition: background 0.15s; }
    .us-tr:hover { background: #fafbfc; }
    .us-tr-inactive { opacity: 0.6; }
    .us-tr-inactive:hover { opacity: 0.85; }

    .us-check { width: 18px; height: 18px; accent-color: #4f46e5; cursor: pointer; }

    /* User cell */
    .us-user-cell { display: flex; align-items: center; gap: 0.85rem; }
    .us-avatar {
        width: 40px; height: 40px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        color: white; font-weight: 700; font-size: 1rem; flex-shrink: 0;
    }
    .us-user-name { font-weight: 600; color: #0f172a; font-size: 0.92rem; }
    .us-user-email { font-size: 0.8rem; color: #64748b; }

    /* Role badge */
    .us-role {
        display: inline-flex; align-items: center; gap: 0.3rem;
        padding: 0.35rem 0.85rem; border-radius: 2rem;
        font-size: 0.8rem; font-weight: 600;
    }
    .us-role-admin { background: #fef2f2; color: #dc2626; }
    .us-role-service_provider { background: #ecfdf5; color: #059669; }
    .us-role-client { background: #eff6ff; color: #2563eb; }

    /* Activity */
    .us-activity { display: flex; gap: 1rem; font-size: 0.82rem; color: #64748b; }
    .us-activity-icon { margin-right: 0.35rem; }
    .us-activity-icon-star { color: #f59e0b; }
    .us-activity-icon-comment { color: #3b82f6; }
    .us-activity-icon-booking { color: #8b5cf6; }

    /* Action buttons */
    .us-action-group { display: flex; gap: 0.35rem; justify-content: flex-end; }
    .us-action-btn {
        width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center;
        background: #f8fafc; color: #475569; border: 1px solid #eef2f6;
        border-radius: 0.65rem; text-decoration: none; font-size: 0.8rem;
        transition: all 0.2s; cursor: pointer;
    }
    .us-action-btn:hover { background: #eef2ff; color: #4f46e5; border-color: rgba(79,70,229,0.2); transform: translateY(-1px); }
    .us-action-btn-danger:hover { background: #fef2f2; color: #dc2626; border-color: rgba(220,38,38,0.2); }

    /* Empty */
    .us-empty { text-align: center; padding: 4rem 2rem; }
    .us-empty-icon { font-size: 3rem; color: #94a3b8; margin-bottom: 1rem; opacity: 0.3; }
    .us-empty h4 { font-weight: 700; color: #0f172a; }
    .us-empty p { color: #475569; margin-bottom: 0; }

    /* Pagination wrap */
    .us-pagination-wrap { padding: 1rem 1.5rem; border-top: 1px solid #eef2f6; }

    /* ===== Responsive ===== */
    @media (max-width: 992px) {
        .us-stats { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 768px) {
        .us-header-title { font-size: 1.35rem; }
        .us-stat { padding: 1rem; }
        .us-stat-value { font-size: 1.35rem; }
        .us-td-date, .us-th:nth-child(6) { display: none; }
    }
    @media (max-width: 576px) {
        .us-stats { grid-template-columns: 1fr; }
        .us-action-btn { width: 30px; height: 30px; font-size: 0.75rem; }
        .us-toggle-text-status { display: none; }
    }
    </style>
</div>
