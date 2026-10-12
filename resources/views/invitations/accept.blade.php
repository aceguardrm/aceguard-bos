<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="referrer" content="no-referrer">
<title>Workspace Invitation | AceGuard BOS</title>
<style>
body { margin: 0; background: #f5f7fa; color: #1e293b; font-family: sans-serif; }
main { max-width: 520px; margin: 60px auto; padding: 28px; background: white; border-radius: 16px; }
label { display: block; margin-top: 18px; }
input { box-sizing: border-box; width: 100%; padding: 12px; margin-top: 8px; border: 1px solid #cbd5e1; border-radius: 8px; }
button, .button { display: inline-block; margin-top: 20px; padding: 12px 20px; background: #2563eb; color: white; border: 0; border-radius: 8px; text-decoration: none; cursor: pointer; }
.errors { color: #b91c1c; }
@media (max-width: 600px) { main { margin: 20px; } }
</style>
</head>
<body>
<main>
<h1>AceGuard BOS invitation</h1>
<p>Workspace: <strong>{{ $invitation->client->company_name }}</strong></p>
<p>Invited email: {{ $invitation->email }}</p>
<p>Role: {{ ucfirst($invitation->role) }}</p>

@if ($errors->any())
<ul class="errors" role="alert">
@foreach ($errors->all() as $error)
<li>{{ $error }}</li>
@endforeach
</ul>
@endif

@if (!auth()->check() && $existingAccount)
<p>Sign in with your invited email address to accept this invitation.</p>
<a class="button" href="{{ route('login') }}">Sign in</a>
@else
<form method="POST" action="{{ route('workspace-invitations.accept', ['token' => $token]) }}">
@csrf
@if (!auth()->check())
<label for="name">Your name</label>
<input id="name" name="name" value="{{ old('name') }}" maxlength="255" autocomplete="name" required>
<label for="password">Password — at least 12 characters</label>
<input id="password" type="password" name="password" minlength="12" autocomplete="new-password" required>
<label for="password_confirmation">Confirm password</label>
<input id="password_confirmation" type="password" name="password_confirmation" minlength="12" autocomplete="new-password" required>
@endif
<button type="submit">{{ auth()->check() ? 'Accept invitation' : 'Create account and accept' }}</button>
</form>
@endif
</main>
</body>
</html>
