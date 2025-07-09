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
  <aside class="hidden sticky top-0 p-6 w-64 h-screen text-white bg-orange-500 md:block">
    <h2 class="mb-6 text-2xl font-bold">Flapa API <small>v1<small> </h2>
    <nav class="space-y-4 text-sm">
      <a href="#new-signin" class="block hover:text-black">New Signin (Wizard)</a>
      <a href="#normal-signin" class="block hover:text-black">Normal Signin</a>
      <a href="#normal-signup" class="block hover:text-black">Normal Sign Up</a>
      <a href="#get-user" class="block hover:text-black">Get User Details</a>
      <a href="#update-user" class="block hover:text-black">Update User</a>
      <a href="#supported-languages" class="block hover:text-black">Supported Languages</a>
      <a href="#system-items" class="block hover:text-black">System Items</a>
      <a href="#listings" class="block hover:text-black">Listings</a>
      <a href="#property-types" class="block hover:text-black">Property Types</a>
      <a href="#host-signup" class="block hover:text-black">Host Signup</a>
      <a href="#verification-status" class="block hover:text-black">Verification Status</a>
    </nav>
  </aside>

  <!-- Main Content -->
  <main class="flex-1 p-6">
    <!-- Mobile Nav -->
    <div class="mb-4 md:hidden">
      <select onchange="location = this.value" class="p-2 w-full text-orange-600 rounded border border-orange-500">
        <option value="#new-signin">New Signin (Wizard)</option>
        <option value="#normal-signin">Normal Signin</option>
        <option value="#normal-signup">Normal Sign Up</option>
        <option value="#get-user">Get User Details</option>
        <option value="#update-user">Update User</option>
        <option value="#supported-languages">Supported Languages</option>
        <option value="#system-items">System Items</option>
        <option value="#listings">Listings</option>
        <option value="#property-types">Property Types</option>
      </select>
    </div>

    <!-- Sections -->
    <section class="mb-8 p-4 bg-gray-900 text-white rounded-lg border-l-4 border-orange-500 shadow">
      <h2 class="mb-2 text-xl font-bold text-orange-300">Authentication</h2>
      <p class="mb-2">
        Most API endpoints require authentication. You must obtain an access token by logging in (see <a href="#new-signin" class="text-orange-400 underline">New Signin</a> or <a href="#normal-signin" class="text-orange-400 underline">Normal Signin</a>), or by using OTP-based authentication.
      </p>
      <ul class="mb-2 list-disc ml-6">
        <li>Include your token in the <code class="bg-gray-800 px-1 rounded">Authorization</code> header for all authenticated requests.</li>
        <li>Format: <code class="bg-gray-800 px-1 rounded">Authorization: Bearer &lt;token&gt;</code></li>
        <li>Tokens are required for creating, updating, or deleting resources, and for accessing user-specific data.</li>
      </ul>
      <div class="mb-2">
        <strong class="text-orange-200">Example: Authenticated Request</strong>
        <pre class="bg-gray-800 text-orange-200 p-2 rounded text-xs overflow-x-auto">
Authorization: Bearer &lt;your_token_here&gt;</pre>
      </div>
      <p class="text-sm text-orange-200">If you do not provide a valid token, you will receive a 401 Unauthorized error.</p>
    </section>
    @include('documentation.sections.host-signup')
    @include('documentation.sections.verification-status')
    @include('documentation.sections.new-signin')
    @include('documentation.sections.normal-signin')
    @include('documentation.sections.normal-signup')
    @include('documentation.sections.get-user')
    @include('documentation.sections.update-user')
    @include('documentation.sections.supported-languages')
    @include('documentation.sections.system-items')
    @include('documentation.sections.listings')
    @include('documentation.sections.property-types')

    <footer class="mt-20 text-sm text-center text-gray-500">
      &copy; 2025 Flapa API Docs. All rights reserved.
    </footer>
  </main>

</body>
</html>
