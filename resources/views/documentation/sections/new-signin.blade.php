<!-- New Signin (Wizard) Section -->
<section id="new-signin" class="mb-12">
  <h2 class="mb-4 text-2xl font-bold text-orange-600">New Signin (Wizard via PHONE)</h2>
  <ol class="space-y-2 list-decimal list-inside">
    <li>Enters phone to receive an SMS – <code class="px-1 bg-gray-100 rounded">Step1 – GetPhoneOTP</code></li>
    <span class="inline-block bg-black text-white px-2 py-1 rounded font-mono text-sm">POST {base_url}/get-phone-otp</span>

    <li>Temp account will be created and OTP SMS is sent to user</li>
    
    <li>User enters OTP to be verified – <code class="px-1 bg-gray-100 rounded">Step2 – verifyOtpViaPhone</code></li>
    <span class="inline-block bg-black text-white px-2 py-1 rounded font-mono text-sm">POST {base_url}/verify-otp-byphone</span>

    <li>Once otp is success progress the user to the NEXT step for the to enter details</li>
    <li>User enters their personal details and submits – <code class="px-1 bg-gray-100 rounded">Step3 – RegisterUserDetails</code></li>
    <span class="inline-block bg-black text-white px-2 py-1 rounded font-mono text-sm">POST [base_url]/register-user-details</span>
    
    <li>Details are updated to the temp account and user is authenticated expect a TOKEN</li>

  </ol>
</section> 

<!-- New Signin (Wizard) Section -->
<section id="new-signin" class="mb-12">
  <h2 class="mb-4 text-2xl font-bold text-orange-600">New Signin (Wizard via EMAIL)</h2>
  <ol class="space-y-2 list-decimal list-inside">
    <li>Enters phone to receive an SMS – <code class="px-1 bg-gray-100 rounded">Step1 – GetPhoneOTP</code></li>
    <span class="inline-block bg-black text-white px-2 py-1 rounded font-mono text-sm">POST {base_url}/get-email-otp</span>

    <li>Temp account will be created and OTP SMS is sent to user</li>
    
    <li>User enters OTP to be verified – <code class="px-1 bg-gray-100 rounded">Step2 – verifyOtpViaPhone</code></li>
    <span class="inline-block bg-black text-white px-2 py-1 rounded font-mono text-sm">POST {base_url}/verify-otp-byemail</span>

    <li>Once otp is success progress the user to the NEXT step for the to enter details</li>
    <li>User enters their personal details and submits – <code class="px-1 bg-gray-100 rounded">Step3 – RegisterUserDetails</code></li>
    <span class="inline-block bg-black text-white px-2 py-1 rounded font-mono text-sm">POST {base_url}/register-user-details</span>
    
    <li>Details are updated to the temp account and user is authenticated respect a TOKEN</li>

  </ol>
</section> 