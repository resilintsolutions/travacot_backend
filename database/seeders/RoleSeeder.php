<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder {
  public function run() {
    Role::firstOrCreate(['name'=>'admin']);
    Role::firstOrCreate(['name'=>'customer']);
    Role::firstOrCreate(['name'=>'agent']);

    $user = User::firstOrCreate(['email'=>'admin@example.com'], [
      'name'=>'Admin',
      'password'=>Hash::make('secret123')
    ]);
    $user->assignRole('admin');
  }
}
