<!-- Get User Details Section -->
<section id="get-user" class="mb-12">
  <h2 class="mb-4 text-2xl font-bold text-orange-600">Get User Details</h2>
  <p>Use <code>$user_id</code> and <code>token</code> from login response.</p>
  <div class="list-disc list-inside">
    <span><code class="block p-2 mt-1 text-sm text-white bg-black rounded">POST  https://{base_url}/users/{{$user_id}}</code></span>
  </div>
  <pre class="overflow-x-auto p-4 mt-4 text-sm bg-gray-100 rounded">
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

</section> 