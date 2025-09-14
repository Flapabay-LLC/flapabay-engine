<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\PaymentMethod;
use App\Models\PayoutOption;
use Illuminate\Support\Facades\Validator;

class PayoutMethodsController extends Controller
{
    /**
     * Get supported payout methods by country
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getSupportedPayoutMethods(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'country_code' => 'required|string|size:2'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $countryCode = strtoupper($request->country_code);
            
            // Define supported methods by country
            $supportedMethods = $this->getMethodsByCountry($countryCode);

            return response()->json([
                'success' => true,
                'data' => [
                    'country_code' => $countryCode,
                    'country_name' => $this->getCountryName($countryCode),
                    'currency' => $this->getCountryCurrency($countryCode),
                    'supported_methods' => $supportedMethods
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve supported methods',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's payout methods with enhanced details
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getUserPayoutMethods(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            if (!$user->isHost()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only hosts can manage payout methods',
                ], 403);
            }

            $defaultPayoutMethodId = $user->userDetail?->default_payout_method_id;
            
            $payoutMethods = $user->paymentMethods()
                ->select('id', 'type', 'payment_method', 'account_number', 'country_code', 'currency', 'created_at')
                ->get()
                ->map(function ($method) use ($defaultPayoutMethodId) {
                    return [
                        'id' => $method->id,
                        'type' => $method->type,
                        'method' => $method->payment_method,
                        'account_number' => $this->maskAccountNumber($method->account_number),
                        'country_code' => $method->country_code,
                        'currency' => $method->currency,
                        'is_default' => $method->id == $defaultPayoutMethodId,
                        'created_at' => $method->created_at,
                        'provider_info' => $this->getProviderInfo($method->payment_method, $method->country_code)
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'payout_methods' => $payoutMethods,
                    'total_methods' => $payoutMethods->count()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve payout methods',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new payout method
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createPayoutMethod(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            if (!$user->isHost()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only hosts can create payout methods',
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'type' => 'required|string|in:bank_transfer,mobile_money,digital_wallet',
                'payment_method' => 'required|string',
                'country_code' => 'required|string|size:2',
                'currency' => 'required|string|size:3',
                'account_number' => 'required|string',
                'bank_name' => 'nullable|string',
                'account_holder_name' => 'nullable|string',
                'routing_number' => 'nullable|string',
                'swift_code' => 'nullable|string',
                'phone_number' => 'nullable|string',
                'is_default' => 'nullable|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Validate method is supported in country
            $supportedMethods = $this->getMethodsByCountry($request->country_code);
            $methodSupported = collect($supportedMethods)->contains(function ($method) use ($request) {
                return $method['method'] === $request->payment_method;
            });

            if (!$methodSupported) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment method not supported in this country'
                ], 422);
            }

            // Create the payout method
            $payoutMethod = PaymentMethod::create([
                'user_id' => $user->id,
                'type' => $request->type,
                'payment_method' => $request->payment_method,
                'account_number' => $request->account_number,
                'country_code' => strtoupper($request->country_code),
                'currency' => strtoupper($request->currency),
                // Store additional data as JSON in a metadata field if needed
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payout method created successfully',
                'data' => [
                    'id' => $payoutMethod->id,
                    'type' => $payoutMethod->type,
                    'method' => $payoutMethod->payment_method,
                    'account_number' => $this->maskAccountNumber($payoutMethod->account_number),
                    'country_code' => $payoutMethod->country_code,
                    'currency' => $payoutMethod->currency,
                    'created_at' => $payoutMethod->created_at
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create payout method',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a payout method
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function updatePayoutMethod(Request $request, $id): JsonResponse
    {
        try {
            $user = $request->user();
            
            if (!$user->isHost()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only hosts can update payout methods',
                ], 403);
            }

            $payoutMethod = PaymentMethod::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$payoutMethod) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payout method not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'account_number' => 'sometimes|required|string',
                'bank_name' => 'nullable|string',
                'account_holder_name' => 'nullable|string',
                'routing_number' => 'nullable|string',
                'swift_code' => 'nullable|string',
                'phone_number' => 'nullable|string',
                'is_default' => 'nullable|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Update only provided fields
            $updateData = $request->only(['account_number']);
            $payoutMethod->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Payout method updated successfully',
                'data' => [
                    'id' => $payoutMethod->id,
                    'type' => $payoutMethod->type,
                    'method' => $payoutMethod->payment_method,
                    'account_number' => $this->maskAccountNumber($payoutMethod->account_number),
                    'country_code' => $payoutMethod->country_code,
                    'currency' => $payoutMethod->currency,
                    'updated_at' => $payoutMethod->updated_at
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update payout method',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a payout method
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function deletePayoutMethod(Request $request, $id): JsonResponse
    {
        try {
            $user = $request->user();
            
            if (!$user->isHost()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only hosts can delete payout methods',
                ], 403);
            }

            $payoutMethod = PaymentMethod::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$payoutMethod) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payout method not found'
                ], 404);
            }

            // Check if method is being used in pending withdrawals
            $pendingWithdrawals = Withdrawal::where('payment_method_id', $id)
                ->whereIn('status', ['pending', 'processing'])
                ->count();

            if ($pendingWithdrawals > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete payout method with pending withdrawals'
                ], 422);
            }

            $payoutMethod->delete();

            return response()->json([
                'success' => true,
                'message' => 'Payout method deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete payout method',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get supported methods by country
     *
     * @param string $countryCode
     * @return array
     */
    private function getMethodsByCountry(string $countryCode): array
    {
        $methods = [
            'US' => [
                ['method' => 'bank_transfer', 'name' => 'Bank Transfer', 'type' => 'bank_transfer', 'providers' => ['Chase', 'Bank of America', 'Wells Fargo', 'Citibank', 'US Bank', 'PNC Bank'], 'fields' => ['account_number', 'routing_number', 'account_holder_name'], 'description' => 'Direct bank transfer via ACH'],
                ['method' => 'paypal', 'name' => 'PayPal', 'type' => 'digital_wallet', 'providers' => ['PayPal'], 'fields' => ['email'], 'description' => 'PayPal digital wallet'],
                ['method' => 'stripe', 'name' => 'Stripe Connect', 'type' => 'digital_wallet', 'providers' => ['Stripe'], 'fields' => ['account_number'], 'description' => 'Stripe Connect account']
            ],
            'GB' => [
                ['method' => 'bank_transfer', 'name' => 'Bank Transfer', 'type' => 'bank_transfer', 'providers' => ['Barclays', 'HSBC', 'Lloyds', 'NatWest', 'Santander', 'TSB'], 'fields' => ['account_number', 'sort_code', 'account_holder_name'], 'description' => 'UK bank transfer via Faster Payments'],
                ['method' => 'paypal', 'name' => 'PayPal', 'type' => 'digital_wallet', 'providers' => ['PayPal'], 'fields' => ['email'], 'description' => 'PayPal digital wallet']
            ],
            'KE' => [
                ['method' => 'mpesa', 'name' => 'M-Pesa', 'type' => 'mobile_money', 'providers' => ['Safaricom'], 'fields' => ['phone_number'], 'description' => 'M-Pesa mobile money transfer'],
                ['method' => 'airtel_money', 'name' => 'Airtel Money', 'type' => 'mobile_money', 'providers' => ['Airtel'], 'fields' => ['phone_number'], 'description' => 'Airtel Money mobile transfer'],
                ['method' => 'bank_transfer', 'name' => 'Bank Transfer', 'type' => 'bank_transfer', 'providers' => ['KCB', 'Equity Bank', 'Cooperative Bank', 'Standard Chartered', 'Absa Bank'], 'fields' => ['account_number', 'bank_code', 'account_holder_name'], 'description' => 'Kenyan bank transfer']
            ],
            'NG' => [
                ['method' => 'bank_transfer', 'name' => 'Bank Transfer', 'type' => 'bank_transfer', 'providers' => ['GTBank', 'First Bank', 'Access Bank', 'Zenith Bank', 'UBA', 'Fidelity Bank', 'Sterling Bank'], 'fields' => ['account_number', 'bank_code', 'account_holder_name'], 'description' => 'Nigerian bank transfer'],
                ['method' => 'opay', 'name' => 'OPay', 'type' => 'mobile_money', 'providers' => ['OPay'], 'fields' => ['phone_number'], 'description' => 'OPay mobile wallet'],
                ['method' => 'paga', 'name' => 'Paga', 'type' => 'mobile_money', 'providers' => ['Paga'], 'fields' => ['phone_number'], 'description' => 'Paga mobile money']
            ],
            'IN' => [
                ['method' => 'bank_transfer', 'name' => 'Bank Transfer', 'type' => 'bank_transfer', 'providers' => ['SBI', 'HDFC', 'ICICI', 'Axis Bank', 'Kotak Mahindra', 'Yes Bank'], 'fields' => ['account_number', 'ifsc_code', 'account_holder_name'], 'description' => 'Indian bank transfer via NEFT/RTGS'],
                ['method' => 'upi', 'name' => 'UPI', 'type' => 'digital_wallet', 'providers' => ['PhonePe', 'Google Pay', 'Paytm', 'BHIM', 'Amazon Pay'], 'fields' => ['upi_id'], 'description' => 'Unified Payments Interface']
            ],
            'ZA' => [
                ['method' => 'bank_transfer', 'name' => 'Bank Transfer', 'type' => 'bank_transfer', 'providers' => ['Standard Bank', 'ABSA', 'FNB', 'Nedbank', 'Capitec'], 'fields' => ['account_number', 'branch_code', 'account_holder_name'], 'description' => 'South African bank transfer'],
                ['method' => 'paypal', 'name' => 'PayPal', 'type' => 'digital_wallet', 'providers' => ['PayPal'], 'fields' => ['email'], 'description' => 'PayPal digital wallet']
            ],
            'GH' => [
                ['method' => 'mtn_momo', 'name' => 'MTN Mobile Money', 'type' => 'mobile_money', 'providers' => ['MTN'], 'fields' => ['phone_number'], 'description' => 'MTN Mobile Money Ghana'],
                ['method' => 'vodafone_cash', 'name' => 'Vodafone Cash', 'type' => 'mobile_money', 'providers' => ['Vodafone'], 'fields' => ['phone_number'], 'description' => 'Vodafone Cash mobile money'],
                ['method' => 'bank_transfer', 'name' => 'Bank Transfer', 'type' => 'bank_transfer', 'providers' => ['GCB Bank', 'Ecobank', 'Standard Chartered', 'Stanbic Bank'], 'fields' => ['account_number', 'bank_code', 'account_holder_name'], 'description' => 'Ghanaian bank transfer']
            ],
            'UG' => [
                ['method' => 'mtn_momo', 'name' => 'MTN Mobile Money', 'type' => 'mobile_money', 'providers' => ['MTN'], 'fields' => ['phone_number'], 'description' => 'MTN Mobile Money Uganda'],
                ['method' => 'airtel_money', 'name' => 'Airtel Money', 'type' => 'mobile_money', 'providers' => ['Airtel'], 'fields' => ['phone_number'], 'description' => 'Airtel Money Uganda'],
                ['method' => 'bank_transfer', 'name' => 'Bank Transfer', 'type' => 'bank_transfer', 'providers' => ['Stanbic Bank', 'Standard Chartered', 'Centenary Bank', 'DFCU Bank'], 'fields' => ['account_number', 'bank_code', 'account_holder_name'], 'description' => 'Ugandan bank transfer']
            ],
            'CA' => [
                ['method' => 'bank_transfer', 'name' => 'Bank Transfer', 'type' => 'bank_transfer', 'providers' => ['RBC', 'TD Bank', 'Scotiabank', 'BMO', 'CIBC'], 'fields' => ['account_number', 'transit_number', 'institution_number', 'account_holder_name'], 'description' => 'Canadian bank transfer via Interac'],
                ['method' => 'paypal', 'name' => 'PayPal', 'type' => 'digital_wallet', 'providers' => ['PayPal'], 'fields' => ['email'], 'description' => 'PayPal digital wallet']
            ],
            'AU' => [
                ['method' => 'bank_transfer', 'name' => 'Bank Transfer', 'type' => 'bank_transfer', 'providers' => ['Commonwealth Bank', 'Westpac', 'ANZ', 'NAB', 'Bendigo Bank'], 'fields' => ['account_number', 'bsb_code', 'account_holder_name'], 'description' => 'Australian bank transfer'],
                ['method' => 'paypal', 'name' => 'PayPal', 'type' => 'digital_wallet', 'providers' => ['PayPal'], 'fields' => ['email'], 'description' => 'PayPal digital wallet']
            ]
        ];

        return $methods[$countryCode] ?? [
            ['method' => 'bank_transfer', 'name' => 'Bank Transfer', 'type' => 'bank_transfer', 'providers' => ['Local Bank'], 'fields' => ['account_number', 'bank_code', 'account_holder_name'], 'description' => 'Local bank transfer']
        ];
    }

    /**
     * Mask account number for security
     *
     * @param string $accountNumber
     * @return string
     */
    private function maskAccountNumber(string $accountNumber): string
    {
        if (strlen($accountNumber) <= 4) {
            return str_repeat('*', strlen($accountNumber));
        }
        
        return str_repeat('*', strlen($accountNumber) - 4) . substr($accountNumber, -4);
    }

    /**
     * Get provider information for a payment method
     *
     * @param string $method
     * @param string $countryCode
     * @return array
     */
    private function getProviderInfo(string $method, string $countryCode): array
    {
        $supportedMethods = $this->getMethodsByCountry($countryCode);
        
        foreach ($supportedMethods as $supportedMethod) {
            if ($supportedMethod['method'] === $method) {
                return [
                    'name' => $supportedMethod['name'],
                    'providers' => $supportedMethod['providers'],
                    'required_fields' => $supportedMethod['fields']
                ];
            }
        }
        
        return ['name' => ucfirst($method), 'providers' => [], 'required_fields' => []];
    }

    /**
     * Get all supported countries with their payout methods
     *
     * @return JsonResponse
     */
    public function getAllSupportedCountries(): JsonResponse
    {
        try {
            $countries = [];
            $supportedCountries = ['US', 'GB', 'KE', 'NG', 'IN', 'ZA', 'GH', 'UG', 'CA', 'AU', 'ZM'];
            
            foreach ($supportedCountries as $countryCode) {
                $methods = $this->getStructuredMethodsByCountry($countryCode);
                $countries[] = [
                    'code' => $countryCode,
                    'name' => $this->getCountryName($countryCode),
                    'methods' => $methods
                ];
            }
            
            return response()->json([
                'countries' => $countries
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve supported countries',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get structured methods by country for hierarchical vendor structure
     *
     * @param string $countryCode
     * @return array
     */
    private function getStructuredMethodsByCountry(string $countryCode): array
    {
        $methods = [
            'US' => [
                [
                    'type' => 'BankTransfer',
                    'displayName' => 'Bank Transfer',
                    'vendors' => [
                        ['id' => 'chase', 'name' => 'Chase Bank'],
                        ['id' => 'boa', 'name' => 'Bank of America'],
                        ['id' => 'wells_fargo', 'name' => 'Wells Fargo'],
                        ['id' => 'citibank', 'name' => 'Citibank'],
                        ['id' => 'us_bank', 'name' => 'US Bank']
                    ]
                ],
                [
                    'type' => 'DigitalWallet',
                    'displayName' => 'Digital Wallet',
                    'vendors' => [
                        ['id' => 'paypal', 'name' => 'PayPal']
                    ]
                ]
            ],
            'GB' => [
                [
                    'type' => 'BankTransfer',
                    'displayName' => 'Bank Transfer',
                    'vendors' => [
                        ['id' => 'barclays', 'name' => 'Barclays'],
                        ['id' => 'hsbc', 'name' => 'HSBC'],
                        ['id' => 'lloyds', 'name' => 'Lloyds Banking Group'],
                        ['id' => 'natwest', 'name' => 'NatWest'],
                        ['id' => 'santander', 'name' => 'Santander UK']
                    ]
                ],
                [
                    'type' => 'DigitalWallet',
                    'displayName' => 'Digital Wallet',
                    'vendors' => [
                        ['id' => 'paypal', 'name' => 'PayPal']
                    ]
                ]
            ],
            'KE' => [
                [
                    'type' => 'MobileMoney',
                    'displayName' => 'Mobile Money',
                    'vendors' => [
                        ['id' => 'mpesa', 'name' => 'M-Pesa'],
                        ['id' => 'airtel', 'name' => 'Airtel Money']
                    ]
                ],
                [
                    'type' => 'BankTransfer',
                    'displayName' => 'Bank Transfer',
                    'vendors' => [
                        ['id' => 'kcb', 'name' => 'KCB Bank'],
                        ['id' => 'equity', 'name' => 'Equity Bank'],
                        ['id' => 'cooperative', 'name' => 'Cooperative Bank'],
                        ['id' => 'standard_chartered', 'name' => 'Standard Chartered'],
                        ['id' => 'absa', 'name' => 'Absa Bank Kenya']
                    ]
                ]
            ],
            'NG' => [
                [
                    'type' => 'BankTransfer',
                    'displayName' => 'Bank Transfer',
                    'vendors' => [
                        ['id' => 'gtbank', 'name' => 'Guaranty Trust Bank'],
                        ['id' => 'access', 'name' => 'Access Bank'],
                        ['id' => 'first_bank', 'name' => 'First Bank of Nigeria'],
                        ['id' => 'uba', 'name' => 'United Bank for Africa'],
                        ['id' => 'zenith', 'name' => 'Zenith Bank'],
                        ['id' => 'fidelity', 'name' => 'Fidelity Bank']
                    ]
                ]
            ],
            'IN' => [
                [
                    'type' => 'BankTransfer',
                    'displayName' => 'Bank Transfer',
                    'vendors' => [
                        ['id' => 'sbi', 'name' => 'State Bank of India'],
                        ['id' => 'hdfc', 'name' => 'HDFC Bank'],
                        ['id' => 'icici', 'name' => 'ICICI Bank'],
                        ['id' => 'axis', 'name' => 'Axis Bank'],
                        ['id' => 'kotak', 'name' => 'Kotak Mahindra Bank']
                    ]
                ],
                [
                    'type' => 'DigitalPayment',
                    'displayName' => 'UPI Payments',
                    'vendors' => [
                        ['id' => 'phonepe', 'name' => 'PhonePe'],
                        ['id' => 'googlepay', 'name' => 'Google Pay'],
                        ['id' => 'paytm', 'name' => 'Paytm'],
                        ['id' => 'bhim', 'name' => 'BHIM UPI']
                    ]
                ]
            ],
            'ZA' => [
                [
                    'type' => 'BankTransfer',
                    'displayName' => 'Bank Transfer',
                    'vendors' => [
                        ['id' => 'standard_bank', 'name' => 'Standard Bank'],
                        ['id' => 'fnb', 'name' => 'First National Bank'],
                        ['id' => 'absa', 'name' => 'ABSA Bank'],
                        ['id' => 'nedbank', 'name' => 'Nedbank'],
                        ['id' => 'capitec', 'name' => 'Capitec Bank']
                    ]
                ]
            ],
            'GH' => [
                [
                    'type' => 'MobileMoney',
                    'displayName' => 'Mobile Money',
                    'vendors' => [
                        ['id' => 'mtn', 'name' => 'MTN Mobile Money'],
                        ['id' => 'vodafone', 'name' => 'Vodafone Cash'],
                        ['id' => 'airteltigo', 'name' => 'AirtelTigo Money']
                    ]
                ],
                [
                    'type' => 'BankTransfer',
                    'displayName' => 'Bank Transfer',
                    'vendors' => [
                        ['id' => 'gcb', 'name' => 'GCB Bank'],
                        ['id' => 'ecobank', 'name' => 'Ecobank Ghana'],
                        ['id' => 'standard_chartered', 'name' => 'Standard Chartered'],
                        ['id' => 'stanbic', 'name' => 'Stanbic Bank']
                    ]
                ]
            ],
            'UG' => [
                [
                    'type' => 'MobileMoney',
                    'displayName' => 'Mobile Money',
                    'vendors' => [
                        ['id' => 'mtn', 'name' => 'MTN Mobile Money'],
                        ['id' => 'airtel', 'name' => 'Airtel Money']
                    ]
                ],
                [
                    'type' => 'BankTransfer',
                    'displayName' => 'Bank Transfer',
                    'vendors' => [
                        ['id' => 'stanbic', 'name' => 'Stanbic Bank'],
                        ['id' => 'centenary', 'name' => 'Centenary Bank'],
                        ['id' => 'dfcu', 'name' => 'DFCU Bank'],
                        ['id' => 'equity', 'name' => 'Equity Bank Uganda']
                    ]
                ]
            ],
            'CA' => [
                [
                    'type' => 'BankTransfer',
                    'displayName' => 'Bank Transfer',
                    'vendors' => [
                        ['id' => 'rbc', 'name' => 'Royal Bank of Canada'],
                        ['id' => 'td', 'name' => 'TD Canada Trust'],
                        ['id' => 'scotiabank', 'name' => 'Scotiabank'],
                        ['id' => 'bmo', 'name' => 'Bank of Montreal'],
                        ['id' => 'cibc', 'name' => 'Canadian Imperial Bank']
                    ]
                ]
            ],
            'AU' => [
                [
                    'type' => 'BankTransfer',
                    'displayName' => 'Bank Transfer',
                    'vendors' => [
                        ['id' => 'commonwealth', 'name' => 'Commonwealth Bank'],
                        ['id' => 'westpac', 'name' => 'Westpac'],
                        ['id' => 'anz', 'name' => 'ANZ Bank'],
                        ['id' => 'nab', 'name' => 'National Australia Bank'],
                        ['id' => 'bendigo', 'name' => 'Bendigo Bank']
                    ]
                ]
            ],
            'ZM' => [
                [
                    'type' => 'BankTransfer',
                    'displayName' => 'Bank Transfer',
                    'vendors' => [
                        ['id' => 'stanbic', 'name' => 'Stanbic Bank'],
                        ['id' => 'fnb', 'name' => 'FNB Zambia'],
                        ['id' => 'absa', 'name' => 'ABSA Bank']
                    ]
                ],
                [
                    'type' => 'MobileMoney',
                    'displayName' => 'Mobile Money',
                    'vendors' => [
                        ['id' => 'mtn', 'name' => 'MTN Mobile Money'],
                        ['id' => 'airtel', 'name' => 'Airtel Money'],
                        ['id' => 'zamtel', 'name' => 'Zamtel Kwacha']
                    ]
                ]
            ]
        ];

        return $methods[$countryCode] ?? [
            [
                'type' => 'BankTransfer',
                'displayName' => 'Bank Transfer',
                'vendors' => [
                    ['id' => 'local_bank', 'name' => 'Local Bank']
                ]
            ]
        ];
    }

    /**
     * Get country name from country code
     *
     * @param string $countryCode
     * @return string
     */
    private function getCountryName(string $countryCode): string
    {
        $countries = [
            'US' => 'United States',
            'GB' => 'United Kingdom',
            'KE' => 'Kenya',
            'NG' => 'Nigeria',
            'IN' => 'India',
            'ZA' => 'South Africa',
            'GH' => 'Ghana',
            'UG' => 'Uganda',
            'CA' => 'Canada',
            'AU' => 'Australia',
            'ZM' => 'Zambia'
        ];

        return $countries[$countryCode] ?? 'Unknown Country';
    }

    /**
     * Set a payout method as default for the user
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function setDefaultPayoutMethod(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();
            
            if (!$user->isHost()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only hosts can manage payout methods',
                ], 403);
            }

            // Find the payout method
            $payoutMethod = $user->paymentMethods()->find($id);
            
            if (!$payoutMethod) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payout method not found or does not belong to you',
                ], 404);
            }

            // Update the user's default payout method ID in user_details table
            $userDetail = $user->userDetail;
            if ($userDetail) {
                $userDetail->update(['default_payout_method_id' => $id]);
            } else {
                // Create user detail record if it doesn't exist
                $user->userDetail()->create([
                    'default_payout_method_id' => $id
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Default payout method updated successfully',
                'data' => [
                    'default_payout_method_id' => $id,
                    'method' => [
                        'id' => $payoutMethod->id,
                        'type' => $payoutMethod->type,
                        'method' => $payoutMethod->payment_method,
                        'account_number' => $this->maskAccountNumber($payoutMethod->account_number),
                        'country_code' => $payoutMethod->country_code,
                        'currency' => $payoutMethod->currency
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to set default payout method',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get country currency from country code
     *
     * @param string $countryCode
     * @return string
     */
    private function getCountryCurrency(string $countryCode): string
    {
        $currencies = [
            'US' => 'USD',
            'GB' => 'GBP',
            'KE' => 'KES',
            'NG' => 'NGN',
            'IN' => 'INR',
            'ZA' => 'ZAR',
            'GH' => 'GHS',
            'UG' => 'UGX',
            'CA' => 'CAD',
            'AU' => 'AUD',
            'ZM' => 'ZMW'
        ];

        return $currencies[$countryCode] ?? 'USD';
    }
}