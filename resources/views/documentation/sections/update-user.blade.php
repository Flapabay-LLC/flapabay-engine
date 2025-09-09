<!-- Update User Details Section -->
<section id="update-user">
  <h2 class="mb-4 text-2xl font-bold text-orange-600">Update User Details</h2>
  <p>Use PATCH/POST on the <code>/users/{id}</code> endpoint with user token to update user details like <code>fname, lname, email, phone</code>, etc.</p>
 
    <pre><code class="block p-2 mt-1 text-sm text-white bg-black rounded">https://{base_url}/users/{{$user_id}}</code></pre>

    <p>The request body should be of type <strong>form-data</strong> and include the following parameters:</p>
    <pre class="overflow-x-auto p-4 mt-4 text-sm bg-gray-100 rounded">
        {
            "id": "text (optional)",
            "user_id": "text (optional)",
            "fname": "text (optional)",
            "lname": "text (optional)",
            "email": "text (optional)",
            "currency": "text (optional)",
            "phone": "text (optional)",
            "two_factor_confirmed_at": "text (optional)",
            "otp": "text (optional)",
            "otp_expires_at": "text (optional)",
            "otp_verified_at": "text (optional)",
            "email_verified_at": "text (optional)",
            "current_team_id": "text (optional)",
            "profile_photo_path": "text (optional)",
            "created_at": "text (optional)",
            "updated_at": "text (optional)",
            "facebook_id": "text (optional)",
            "google_id": "text (optional)",
            "profile_photo_url": "text (optional)",
            "user_id": "text (optional)",
            "bio": "text (optional)",
            "live_in": "text (optional)",
            "contact_email": "text (optional)",
            "phone_2": "text (optional)",
            "languages": "text (optional)",
            "website": "text (optional)",
            "skype": "text (optional)",
            "facebook": "text (optional)",
            "twitter": "text (optional)",
            "linkedin": "text (optional)",
            "youtube": "text (optional)",
            "profile_picture_url": "text (optional)",
            "dob": "text (optional)"
          }
        </pre>

    <h4>Response</h4>
    <p>The response will be in <strong>JSON</strong> format and include:</p>

    <pre class="overflow-x-auto p-4 mt-4 text-sm bg-gray-100 rounded"><code>{
  "success": true,
  "message": "User updated successfully",
  "user": {
    "id": 1,
    "user_id": "H123",
    "fname": "John",
    "lname": "Doe",
    "email": "john@example.com",
    "currency": "USD",
    "phone": "123456789",
    "two_factor_confirmed_at": null,
    "otp": null,
    "otp_expires_at": null,
    "otp_verified_at": null,
    "email_verified_at": null,
    "current_team_id": null,
    "profile_photo_path": null,
    "created_at": "2024-01-01T00:00:00Z",
    "updated_at": "2024-01-01T00:00:00Z",
    "facebook_id": null,
    "google_id": null,
    "profile_photo_url": null,
    "user_id": 1,
    "bio": "Bio goes here",
    "live_in": "Nairobi",
    "contact_email": "alt@example.com",
    "phone_2": "0987654321",
    "languages": "English, French",
    "website": "https://example.com",
    "skype": "skypeid",
    "facebook": "https://facebook.com/example",
    "twitter": "https://twitter.com/example",
    "linkedin": "https://linkedin.com/in/example",
    "youtube": "https://youtube.com/example",
    "profile_picture_url": "https://cdn.example.com/user.jpg",
    "dob": "1990-01-01"
  }
}
</code></pre>
</section> 