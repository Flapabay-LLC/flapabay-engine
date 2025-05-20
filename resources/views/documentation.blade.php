<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Flapa API Documentation</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex min-h-screen text-black bg-white">

  <!-- Sidebar -->
  <aside class="sticky top-0 hidden w-64 h-screen p-6 text-white bg-orange-500 md:block">
    <h2 class="mb-6 text-2xl font-bold">Flapa API</h2>
    <nav class="space-y-4 text-sm">
      <a href="#new-signin" class="block hover:text-black">New Signin (Wizard)</a>
      <a href="#normal-signin" class="block hover:text-black">Normal Signin</a>
      <a href="#normal-signup" class="block hover:text-black">Normal Sign Up</a>
      <a href="#get-user" class="block hover:text-black">Get User Details</a>
      <a href="#update-user" class="block hover:text-black">Update User</a>
    </nav>
  </aside>

  <!-- Main Content -->
  <main class="flex-1 p-6">
    <!-- Mobile Nav -->
    <div class="mb-4 md:hidden">
      <select onchange="location = this.value" class="w-full p-2 text-orange-600 border border-orange-500 rounded">
        <option value="#new-signin">New Signin (Wizard)</option>
        <option value="#normal-signin">Normal Signin</option>
        <option value="#normal-signup">Normal Sign Up</option>
        <option value="#get-user">Get User Details</option>
        <option value="#update-user">Update User</option>
      </select>
    </div>

    <!-- Sections -->
    <section id="new-signin" class="mb-12">
      <h2 class="mb-4 text-2xl font-bold text-orange-600">New Signin (Wizard)</h2>
      <ol class="space-y-2 list-decimal list-inside">
        <li>Enters phone to receive an SMS – <code class="px-1 bg-gray-100 rounded">Step1 – GetPhoneOTP</code></li>
        <li>Temp account created and OTP SMS is sent to user</li>
        <li>User enters OTP and successfully proceeds – <code class="px-1 bg-gray-100 rounded">Step2 – verifyOtpViaPhone</code></li>
        <li>User enters their personal details and submits – <code class="px-1 bg-gray-100 rounded">Step3 – RegisterUserDetails</code></li>
        <li>Details are updated to the temp account and proceed to the next</li>
      </ol>
    </section>

    <section id="normal-signin" class="mb-12">
      <h2 class="mb-4 text-2xl font-bold text-orange-600">Normal Signin</h2>
      <ul class="space-y-2 list-disc list-inside">
        <li>User directly uses phone to sign in – <code>SignInWithPhone</code></li>
        <li>User directly uses email to sign in – <code>SignInWithEmail</code></li>
      </ul>
    </section>

    <section id="normal-signup" class="mb-12">
      <h2 class="mb-4 text-2xl font-bold text-orange-600">Normal Sign Up (Register)</h2>
      <p>User directly registers a first-time account.</p>
      <p class="mt-2 text-sm">In Postman:</p>
      <code class="block p-2 mt-1 text-sm text-white bg-black rounded">
        POST {{$base_url}}/register
      </code>
    </section>

    <section id="get-user" class="mb-12">
      <h2 class="mb-4 text-2xl font-bold text-orange-600">Get User Details</h2>
      <p>Use <code>$user_id</code> and <code>token</code> from login response.</p>
      <pre class="p-4 mt-4 overflow-x-auto text-sm bg-gray-100 rounded">
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 7,
      "fname": "cynda",
      "lname": "xavier",
      "email": "nyeleti.bremah@gmail.com",
      "currency": "USD",
      "phone": "260975743472",
      "dob": "1994-04-16"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOi..."
  }
}
      </pre>
      <p class="mt-4 font-semibold">Endpoints:</p>
      <ul class="list-disc list-inside">
        <li><code>{{$base_url}}/users/{{$user_id}}</code></li>
        <li><code>{{$base_url}}/users/13</code></li>
      </ul>
    </section>

    <section id="update-user">
      <h2 class="mb-4 text-2xl font-bold text-orange-600">Update User Details</h2>
      <p>Use PATCH/POST on the <code>/users/{id}</code> endpoint with user token to update user details like <code>fname, lname, email, phone</code>, etc.</p>
    </section>

    <footer class="mt-20 text-sm text-center text-gray-500">
      &copy; 2025 Flapa API Docs. All rights reserved.
    </footer>
  </main>

</body>
</html>
