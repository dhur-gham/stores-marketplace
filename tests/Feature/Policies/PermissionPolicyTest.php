<?php

use App\Models\User;
use App\Policies\PermissionPolicy;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->policy = new PermissionPolicy;
    $this->permission = Permission::firstOrCreate(['name' => 'test_permission', 'guard_name' => 'web']);
});

test('user with view_any_permissions can view any permissions', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::firstOrCreate(['name' => 'view_any_permissions', 'guard_name' => 'web']));

    expect($this->policy->viewAny($user))->toBeTrue();
});

test('user without view_any_permissions cannot view any permissions', function () {
    $user = User::factory()->create();

    expect($this->policy->viewAny($user))->toBeFalse();
});

test('user with view_permissions can view a permission', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::firstOrCreate(['name' => 'view_permissions', 'guard_name' => 'web']));

    expect($this->policy->view($user, $this->permission))->toBeTrue();
});

test('user without view_permissions cannot view a permission', function () {
    $user = User::factory()->create();

    expect($this->policy->view($user, $this->permission))->toBeFalse();
});

test('user with create_permissions can create permissions', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::firstOrCreate(['name' => 'create_permissions', 'guard_name' => 'web']));

    expect($this->policy->create($user))->toBeTrue();
});

test('user without create_permissions cannot create permissions', function () {
    $user = User::factory()->create();

    expect($this->policy->create($user))->toBeFalse();
});

test('user with update_permissions can update a permission', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::firstOrCreate(['name' => 'update_permissions', 'guard_name' => 'web']));

    expect($this->policy->update($user, $this->permission))->toBeTrue();
});

test('user without update_permissions cannot update a permission', function () {
    $user = User::factory()->create();

    expect($this->policy->update($user, $this->permission))->toBeFalse();
});

test('user with delete_permissions can delete a permission', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::firstOrCreate(['name' => 'delete_permissions', 'guard_name' => 'web']));

    expect($this->policy->delete($user, $this->permission))->toBeTrue();
});

test('user without delete_permissions cannot delete a permission', function () {
    $user = User::factory()->create();

    expect($this->policy->delete($user, $this->permission))->toBeFalse();
});
