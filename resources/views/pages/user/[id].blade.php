<?php
use Illuminate\Support\Facades\Http;
use function Laravel\Folio\{middleware, name};
use function Livewire\Volt\{state, with};
use App\Actions\UploadImage;
use Livewire\Volt\Component;
use App\Models\User;
use App\Models\Media;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

name('user.edit');
middleware(['auth', 'verified', 'can:access-super-admin-panel' ]);

new class extends Component {
    use WithPagination;
    use WithFileUploads;

    public $id;
    public $name;
    public $image;
    public $password;
    public $email;
    public $existingImage = null;
    public $imageUploaded = false;

    public function mount($id): void
    {
        $this->id = $id;
        $user = User::find($this->id);
        if ($user) {
            $this->name = $user->name;
            $this->email = $user->email;
        }
    }

    // Generate email from name if email is empty
    public function updatedName()
    {
        if (empty($this->email)) {
            $this->email = \Illuminate\Support\Str::email($this->name);
        }
    }

    public function with(): array
    {
        return [
            'category' => User::query()->find($this->id),
        ];
    }

    public function submit(): void
    {
        // Validate the input
        $data = $this->validate([
            'name' => 'required|string|max:255',
            'password' => 'nullable',
            'email' => 'email|max:255',
        ]);

        $user = User::find($this->id);

        if (!$user) {
            session()->flash('error', 'User not found!');
            return;
        }


        $user->update([
            'name' => $this->name,
            'email' => $this->email ,
        ]);

        if ($this->password != null) {
            $user->update([
                'password' => \Illuminate\Support\Facades\Hash::make($this->password),
            ]);

        }

        session()->flash('message', 'User updated successfully!');
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
        <div class="flex justify-between items-center">
        <h2 class="text-lg font-semibold leading-tight text-gray-800 dark:text-gray-200">
            {{ __('Edit User') }}
        </h2>
            <a href="/ideas/{{ $id }}" class="bg-orange-700 hover:bg-orange-800 text-white font-bold py-2 px-4 rounded">View</a>
</div>
    </x-slot>
    @volt('user.edit')
    <div>
        <form wire:submit.prevent="submit" class="space-y-4">
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
            <x-ui.input wire:model="email" type="email" label="Email" id="email" name="email" />
            <x-ui.input wire:model="password" type="password" label="Password" id="password" password="password" />
            <div class="my-4">
                <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded">
                    Update
                </button>
            </div>
        </form>
    </div>
    @endvolt
</x-layouts.app>
