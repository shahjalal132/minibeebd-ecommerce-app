<?php
use Spatie\Permission\Models\Role;
$roles = Role::pluck('name')->toArray();
echo implode(', ', $roles);
