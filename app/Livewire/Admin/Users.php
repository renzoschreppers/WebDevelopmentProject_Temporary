<?php

namespace App\Livewire\Admin;

use App\Livewire\Forms\UserForm;
use App\Models\User;
use App\Traits\NotificationsTrait;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.canteen', ['title' => 'Users'])]
class Users extends Component
{
    use NotificationsTrait;
    use WithPagination;

    public UserForm $form;

    public bool $showModal = false;

    #[Url]
    public string $search = '';

    #[Url]
    public string $role = 'all';

    public string $sortColumn = 'name';

    public string $sortDirection = 'asc';

    public int $perPage = 10;

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'role'])) {
            $this->resetPage();
        }
    }

    public function resort(string $column): void
    {
        if ($this->sortColumn === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortColumn = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'role']);
        $this->resetPage();
    }

    public function newUser(): void
    {
        $this->form->reset();
        $this->resetValidation();
        $this->showModal = true;
    }

    public function editUser(User $user): void
    {
        $this->resetValidation();
        $this->form->setUser($user);
        $this->showModal = true;
    }

    public function save(): void
    {
        // An admin must not be able to remove their own access.
        if ($this->form->id === auth()->id() && (! $this->form->admin || ! $this->form->active)) {
            $this->toastDanger('You cannot remove your own admin rights or deactivate yourself.');

            return;
        }

        if ($this->form->id) {
            $user = $this->form->update();
            $this->toastSuccess("<b>{$user->name}</b> has been updated.");
        } else {
            $user = $this->form->create();
            $this->toastSuccess("<b>{$user->name}</b> has been created.");
        }

        $this->showModal = false;
        $this->form->reset();
    }

    public function toggleAdmin(User $user): void
    {
        if ($user->id === auth()->id()) {
            $this->toastDanger('You cannot change your own admin rights.');

            return;
        }

        $user->update(['admin' => ! $user->admin]);

        $state = $user->admin ? 'now an administrator' : 'no longer an administrator';
        $this->toastSuccess("<b>{$user->name}</b> is {$state}.");
    }

    public function toggleActive(User $user): void
    {
        if ($user->id === auth()->id()) {
            $this->toastDanger('You cannot deactivate your own account.');

            return;
        }

        $user->update(['active' => ! $user->active]);

        $state = $user->active ? 'activated' : 'deactivated';
        $this->toastSuccess("<b>{$user->name}</b> has been {$state}.");
    }

    public function deleteConfirm(User $user): void
    {
        if ($user->id === auth()->id()) {
            $this->toastDanger('You cannot delete your own account.');

            return;
        }

        $favorites = $user->favorites()->count();

        $warning = $favorites > 0
            ? " Their <b>{$favorites}</b> ".str('favourite')->plural($favorites).' will also be removed.'
            : '';

        $this->confirm(
            "Are you sure you want to delete <b>{$user->name}</b>?{$warning}",
            [
                'heading' => 'Delete user',
                'confirmText' => 'Yes, delete them',
                'next' => [
                    'onEvent' => 'delete-user',
                    'user' => $user->id,
                ],
            ]
        );
    }

    #[On('delete-user')]
    public function deleteUser(User $user): void
    {
        if ($user->id === auth()->id()) {
            return;
        }

        $name = $user->name;
        $user->delete();

        $this->toastSuccess("<b>{$name}</b> has been deleted.");
    }

    public function render()
    {
        $users = User::query()
            ->withCount('favorites')
            ->when($this->search, fn ($query) => $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%");
            }))
            ->when($this->role === 'admins', fn ($query) => $query->where('admin', true))
            ->when($this->role === 'users', fn ($query) => $query->where('admin', false))
            ->when($this->role === 'inactive', fn ($query) => $query->where('active', false))
            ->orderBy($this->sortColumn, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.users', compact('users'));
    }
}
