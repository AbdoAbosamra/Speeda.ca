<?php

use Livewire\Volt\Component;
use App\Models\User;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

new class extends Component
{
    use WithPagination;

    public $search = '';
    public $role = '';
    public $status = '';
    public $showInactive = true;
    public $selectedUsers = [];
    public $selectAll = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'role' => ['except' => ''],
        'status' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedUsers = $this->getUsersProperty()->pluck('id')->map(fn($id) => (string)$id)->toArray();
        } else {
            $this->selectedUsers = [];
        }
    }

    public function updatedSelectedUsers()
    {
        $this->selectAll = count($this->selectedUsers) === $this->getUsersProperty()->count() && $this->getUsersProperty()->count() > 0;
    }

    public function toggleStatus($userId)
    {
        $user = User::findOrFail($userId);
        if ($user->id === auth()->id()) return;

        $user->is_active = !$user->is_active;
        $user->save();

        session()->flash('message', 'Status updated successfully.');
    }

    public function deleteUser($userId)
    {
        $user = User::findOrFail($userId);
        if ($user->id === auth()->id() || $user->role === 'admin') return;

        $user->delete(); // Soft delete
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
        return User::query()
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->role, fn($query) => $query->where('role', $this->role))
            ->when($this->status === 'active', fn($query) => $query->where('is_active', true))
            ->when($this->status === 'inactive', fn($query) => $query->where('is_active', false))
            ->when(!$this->showInactive, fn($query) => $query->where('is_active', true))
            ->withCount(['reviews', 'comments'])
            ->latest()
            ->paginate(15)
            ->withPath('/admin/users');
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

<div class="admin-user-management-container">
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm premium-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body p-4 text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 opacity-75 fw-medium">Total Users</p>
                            <h3 class="mb-0 fw-bold">{{ $stats['total'] }}</h3>
                        </div>
                        <div class="bg-white bg-opacity-20 p-3 rounded-3">
                            <i class="fas fa-users fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm premium-card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                <div class="card-body p-4 text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 opacity-75 fw-medium">Active Users</p>
                            <h3 class="mb-0 fw-bold">{{ $stats['active'] }}</h3>
                        </div>
                        <div class="bg-white bg-opacity-20 p-3 rounded-3">
                            <i class="fas fa-user-check fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm premium-card" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                <div class="card-body p-4 text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 opacity-75 fw-medium">Inactive Users</p>
                            <h3 class="mb-0 fw-bold">{{ $stats['inactive'] }}</h3>
                        </div>
                        <div class="bg-white bg-opacity-20 p-3 rounded-3">
                            <i class="fas fa-user-slash fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm premium-card" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                <div class="card-body p-4 text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 opacity-75 fw-medium">Providers</p>
                            <h3 class="mb-0 fw-bold">{{ $stats['providers'] }}</h3>
                        </div>
                        <div class="bg-white bg-opacity-20 p-3 rounded-3">
                            <i class="fas fa-briefcase fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Bulk Actions -->
    <div class="card border-0 shadow-sm mb-4 bg-white bg-opacity-50" style="backdrop-filter: blur(10px); border-radius: 20px;">
        <div class="card-body p-4">
            <div class="row g-3 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-0 bg-light" placeholder="Search by name or email...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select wire:model.live="role" class="form-select border-0 bg-light">
                        <option value="">All Roles</option>
                        <option value="client">Client</option>
                        <option value="service_provider">Service Provider</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select wire:model.live="status" class="form-select border-0 bg-light">
                        <option value="">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="form-check form-switch mt-1">
                        <input class="form-check-input" type="checkbox" wire:model.live="showInactive" id="showInactive">
                        <label class="form-check-label" for="showInactive">Show Inactive</label>
                    </div>
                </div>
                <div class="col-md-3 text-end">
                    @if(count($selectedUsers) > 0)
                        <div class="dropdown d-inline-block">
                            <button class="btn btn-primary dropdown-toggle rounded-pill px-4" type="button" data-bs-toggle="dropdown">
                                Bulk Actions ({{ count($selectedUsers) }})
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="border-radius: 12px;">
                                <li><a class="dropdown-item py-2" href="#" wire:click.prevent="bulkActivate"><i class="fas fa-check text-success me-2"></i>Activate</a></li>
                                <li><a class="dropdown-item py-2" href="#" wire:click.prevent="bulkDeactivate"><i class="fas fa-ban text-warning me-2"></i>Deactivate</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item py-2 text-danger" href="#" wire:click.prevent="bulkDelete"><i class="fas fa-trash-alt me-2"></i>Move to Trash</a></li>
                            </ul>
                        </div>
                    @endif
                    <a href="{{ route('admin.users.trash') }}" class="btn btn-outline-secondary rounded-pill px-4">
                        <i class="fas fa-trash me-2"></i>Trash Bin
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 20px;">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3" style="width: 40px;">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" wire:model.live="selectAll">
                            </div>
                        </th>
                        <th class="py-3">User Info</th>
                        <th class="py-3">Role</th>
                        <th class="py-3">Activity</th>
                        <th class="py-3">Status</th>
                        <th class="py-3 text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($users as $user)
                        <tr wire:key="user-{{ $user->id }}" class="{{ !$user->is_active ? 'bg-light opacity-75' : '' }}">
                            <td class="ps-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" wire:model.live="selectedUsers" value="{{ $user->id }}">
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle me-3" style="background: {{ $user->is_active ? 'linear-gradient(135deg, #667eea, #764ba2)' : '#cbd5e1' }};">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $user->name }}</div>
                                        <div class="text-muted small">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge rounded-pill px-3 py-2 fw-semibold" 
                                      style="background: {{ $user->role === 'admin' ? '#fee2e2; color: #ef4444' : ($user->role === 'service_provider' ? '#dcfce7; color: #10b981' : '#dbeafe; color: #3b82f6') }};">
                                    {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-3 text-muted small">
                                    <span title="Reviews"><i class="fas fa-star me-1 text-warning"></i>{{ $user->reviews_count }}</span>
                                    <span title="Comments"><i class="fas fa-comment me-1 text-info"></i>{{ $user->comments_count }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" wire:click="toggleStatus({{ $user->id }})" 
                                           {{ $user->is_active ? 'checked' : '' }} 
                                           {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                                    <span class="ms-1 small {{ $user->is_active ? 'text-success' : 'text-muted' }}">
                                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-light rounded-circle me-2" title="Edit">
                                        <i class="fas fa-edit text-primary"></i>
                                    </a>
                                    @if($user->id !== auth()->id() && $user->role !== 'admin')
                                        <button wire:click="deleteUser({{ $user->id }})" wire:confirm="Move this user to trash?" class="btn btn-sm btn-light rounded-circle" title="Delete">
                                            <i class="fas fa-trash-alt text-danger"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-search fa-3x mb-3 d-block opacity-25"></i>
                                No users found matching your criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white border-0 py-3">
            {{ $users->links('components.pagination.default') }}
        </div>
    </div>

    <style>
        .avatar-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.1rem;
        }
        .premium-card {
            transition: transform 0.3s ease;
            cursor: default;
        }
        .premium-card:hover {
            transform: translateY(-5px);
        }
        .table-hover tbody tr:hover {
            background-color: rgba(248, 250, 252, 1);
        }
        .form-check-input:checked {
            background-color: #764ba2;
            border-color: #764ba2;
        }
    </style>
</div>
