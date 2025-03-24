<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

beforeEach(function (): void {
    Route::get('/must-be-confirmed', fn(): string => 'You must be confirmed to see this page.')->middleware(['web', 'password.confirm']);
});

test('a user must confirm their password before visiting a protected page', function (): void {
    $user = User::factory()->create();
    $this->be($user);

    $this->get('/must-be-confirmed')
        ->assertRedirect(route('password.confirm'));
});

test('a user must enter a password to confirm it', function (): void {
    Volt::test('auth.password.confirm')
        ->call('confirm')
        ->assertHasErrors(['password' => 'required']);
});

test('a user must enter their own password to confirm it', function (): void {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    Volt::test('auth.password.confirm')
        ->set('password', 'not-password')
        ->call('confirm')
        ->assertHasErrors(['password' => 'current_password']);
});

test('a user who confirms their password will get redirected', function (): void {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $this->be($user);

    $this->withSession(['url.intended' => '/must-be-confirmed']);

    Volt::test('auth.password.confirm')
        ->set('password', 'password')
        ->call('confirm')
        ->assertRedirect('/must-be-confirmed');
});
