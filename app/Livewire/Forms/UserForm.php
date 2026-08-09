<?php

namespace App\Livewire\Forms;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;

class UserForm extends Form
{
    public ?int $id = null;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public bool $admin = false;

    public bool $active = true;

    protected $validationAttributes = [
        'admin' => 'administrator',
        'active' => 'active state',
    ];

    protected $messages = [
        'name.regex' => 'The name must contain at least one letter.',
    ];

    public function rules(): array
    {
        return [
            'name' => 'required|string|min:2|max:255|regex:/\p{L}/u',
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->id),
            ],
            // Required when creating, optional when editing.
            'password' => $this->id
                ? 'nullable|string|min:8'
                : 'required|string|min:8',
            'admin' => 'boolean',
            'active' => 'boolean',
        ];
    }

    public function setUser(User $user): void
    {
        $this->id = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->admin = $user->admin;
        $this->active = $user->active;
        $this->password = '';
    }

    public function create(): User
    {
        $this->validate();

        return User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'admin' => $this->admin,
            'active' => $this->active,
            'email_verified_at' => now(),
        ]);
    }

    public function update(): User
    {
        $this->validate();

        $user = User::findOrFail($this->id);

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'admin' => $this->admin,
            'active' => $this->active,
        ];

        // Only change the password when one was actually entered.
        if ($this->password !== '') {
            $data['password'] = Hash::make($this->password);
        }

        // Re-verification is required if the address changed.
        if ($user->email !== $this->email) {
            $data['email_verified_at'] = null;
        }

        $user->update($data);

        return $user;
    }
}
