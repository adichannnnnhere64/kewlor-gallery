<?php
use Illuminate\Support\Facades\Http;
use function Laravel\Folio\{middleware, name};
use function Livewire\Volt\{state, with};
use Livewire\Volt\Component;
use App\Models\User;
use Livewire\WithPagination;
use App\Actions\UploadImage;
use Livewire\WithFileUploads;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;

name('user.create');
middleware(['auth', 'verified', 'can:access-super-admin-panel' ]);

new class extends Component {
    use WithPagination;
    use WithFileUploads;

    public $name = '';
    public $password = null;
    public $email = '';

    public function submit(): void
    {
        $data = $this->validate([
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:8',
            'email' => 'email|max:255',
        ]);

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'role' => 'admin',
        ]);


        event(new Registered($user));

        $this->reset(['name', 'email', 'password']);

        session()->flash('message', 'User created successfully.');
    }
};
?>
<style>
    .dark img[alt="Kewlor Logo"] {
        filter: invert(1);
    }
</style>
<x-layouts.app>
    <x-slot name="header">
        <h2 class="text-lg font-semibold leading-tight text-gray-800 dark:text-gray-200">
            {{ __('Create User') }}
        </h2>
    </x-slot>
    @volt('user.create')
    <div>
        <form wire:submit.prevent="submit" class="w-full space-y-4" enctype="multipart/form-data">
            @if (session()->has('message'))
                <div class="p-4 mb-4 text-green-700 bg-green-100 rounded">
                    {{ session('message') }}
                </div>
            @endif

            @if (session()->has('error'))
                <div class="p-4 mb-4 text-red-700 bg-red-100 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <x-ui.input required wire:model="name" label="Name" id="name" name="name" />
            <x-ui.input wire:model="email" label="Email" type="email" id="email" name="email" />
            <x-ui.input wire:model="password" type="password" label="Password" id="password" password="password" />
            <div class="my-4">
                <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded">
                    Create
                </button>
            </div>
        </form>
    </div>
    @endvolt
</x-layouts.app>
