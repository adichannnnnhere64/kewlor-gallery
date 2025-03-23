<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Volt\Volt;

test('can view login page', function (): void {
    $this->get('/auth/login')
        ->assertSuccessful();
});

test('is redirected if already logged in', function (): void {
    $user = User::factory()->create();

    $this->be($user);

    $this->get('/auth/login')
        ->assertRedirect('/home');
});

test('a user can login', function (): void {
    $user = User::factory()->create(['password' => Hash::make('password')]);

    Volt::test('auth.login')
        ->set('email', $user->email)
        ->set('password', 'password')
        ->call('authenticate');

    $this->assertAuthenticatedAs($user);
});

test('is redirected to home after login', function (): void {
    $user = User::factory()->create(['password' => Hash::make('password')]);

    Volt::test('auth.login')
        ->set('email', $user->email)
        ->set('password', 'password')
        ->call('authenticate')
        ->assertRedirect('/');
});

test('email is required', function (): void {
    $user = User::factory()->create(['password' => Hash::make('password')]);

    Volt::test('auth.login')
        ->set('password', 'password')
        ->call('authenticate')
        ->assertHasErrors(['email' => 'required']);
});

test('email must be valid email', function (): void {
    $user = User::factory()->create(['password' => Hash::make('password')]);

    Volt::test('auth.login')
        ->set('email', 'invalid-email')
        ->set('password', 'password')
        ->call('authenticate')
        ->assertHasErrors(['email' => 'email']);
});

test('password is required', function (): void {
    $user = User::factory()->create(['password' => Hash::make('password')]);

    Volt::test('auth.login')
        ->set('email', $user->email)
        ->call('authenticate')
        ->assertHasErrors(['password' => 'required']);
});

test('bad login attempt shows message', function (): void {
    $user = User::factory()->create();

    Volt::test('auth.login')
        ->set('email', $user->email)
        ->set('password', 'bad-password')
        ->call('authenticate')
        ->assertHasErrors('email');

    expect(Auth::check())->toBeFalse();
});
