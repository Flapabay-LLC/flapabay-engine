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
        <option value="#property-types">Property Types</option>
      </select>
    </div>

    <!-- Sections -->
    @include('documentation.sections.new-signin')
    @include('documentation.sections.normal-signin')
    @include('documentation.sections.normal-signup')
    @include('documentation.sections.get-user')
    @include('documentation.sections.update-user')
    @include('documentation.sections.supported-languages')
    @include('documentation.sections.property-types')

    <footer class="mt-20 text-sm text-center text-gray-500">
      &copy; 2025 Flapa API Docs. All rights reserved.
    </footer>
  </main>

</body>
</html>
