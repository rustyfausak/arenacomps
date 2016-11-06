@include('icons.role', ['role' => $role])
@include('icons.spec', [
    'role' => $role,
    'spec' => $spec
])
@include('snippets.role-text', [
    'role' => $role,
    'text' => $spec . ' ' . $role
])
