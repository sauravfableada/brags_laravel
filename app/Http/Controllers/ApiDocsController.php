<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use ReflectionMethod;

class ApiDocsController extends Controller
{
    /**
     * Display the dynamic API documentation dashboard.
     */
    public function index(Request $request)
    {
        $routes = Route::getRoutes();
        $apiData = [];

        foreach ($routes as $route) {
            $uri = $route->uri();

            // Filter only API routes starting with 'api/' or 'api'
            if (!str_starts_with($uri, 'api/') && $uri !== 'api') {
                continue;
            }

            // Exclude API documentation & Swagger internal helper routes
            if (str_contains($uri, 'api-docs') || str_contains($uri, 'documentation') || str_contains($uri, 'oauth2-callback')) {
                continue;
            }

            $methods = array_diff($route->methods(), ['HEAD', 'OPTIONS']);
            if (empty($methods)) {
                continue;
            }
            $primaryMethod = reset($methods);

            $middlewares = (array) $route->middleware();
            $action = $route->getActionName();

            // Determine Role
            $role = 'General';
            $middlewareStr = implode(',', $middlewares);
            if (str_contains($uri, 'admin') || str_contains($middlewareStr, 'role:Admin')) {
                $role = 'Admin';
            } elseif (str_contains($uri, 'seller') || str_contains($middlewareStr, 'role:Seller')) {
                $role = 'Seller';
            } elseif (str_contains($uri, 'customer') || str_contains($middlewareStr, 'role:Customer')) {
                $role = 'Customer';
            }

            // Determine Auth Requirement
            $hasAuth = str_contains($middlewareStr, 'auth');
            $authText = $hasAuth ? 'Bearer Token (Role: ' . $role . ')' : 'Public (Guest)';

            // Generate clean ID
            $cleanUri = trim($uri, '/');
            $id = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $cleanUri) . '-' . strtolower($primaryMethod));

            // Fetch Route-Specific Data Mapping
            $details = $this->getEndpointDetails($cleanUri, strtoupper($primaryMethod), $role, $action, $hasAuth);

            // Headers
            $headers = [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ];
            if ($hasAuth) {
                $headers['Authorization'] = 'Bearer <' . strtoupper($role) . '_ACCESS_TOKEN>';
            }

            $apiData[] = [
                'id' => $id,
                'role' => $role,
                'title' => $details['title'],
                'method' => strtoupper($primaryMethod),
                'path' => '/' . $cleanUri,
                'auth' => $authText,
                'description' => $details['description'],
                'headers' => $headers,
                'params' => $details['params'],
                'requestExample' => $details['requestExample'],
                'responses' => $details['responses']
            ];
        }

        $baseUrl = url('/');

        return view('api-docs', compact('apiData', 'baseUrl'));
    }

    /**
     * Build precise, route-specific title, description, parameters, payload examples, and status code responses.
     */
    private function getEndpointDetails(string $uri, string $method, string $role, string $action, bool $hasAuth): array
    {
        // ----------------------------------------------------
        // ADMIN ENDPOINTS
        // ----------------------------------------------------
        if (str_contains($uri, 'admin/login')) {
            return [
                'title' => 'Admin Login',
                'description' => 'Authenticate an Admin user with login credentials (email/username) & password and issue an admin access token.',
                'params' => [
                    ['name' => 'login', 'type' => 'string', 'required' => true, 'desc' => 'Admin email or username'],
                    ['name' => 'password', 'type' => 'string', 'required' => true, 'desc' => 'Admin account password'],
                    ['name' => 'fcm_token', 'type' => 'string', 'required' => false, 'desc' => 'Firebase Cloud Messaging (FCM) device push notification token']
                ],
                'requestExample' => [
                    'login' => 'admin@brags.co.uk',
                    'password' => 'AdminPassword123!',
                    'fcm_token' => 'fcm_token_sample_device_admin_112233'
                ],
                'responses' => [
                    '200' => [
                        'success' => true,
                        'message' => 'Admin logged in successfully.',
                        'data' => [
                            'user' => ['id' => 1, 'name' => 'Super Admin', 'email' => 'admin@brags.co.uk', 'roles' => [['name' => 'Admin']]],
                            'token' => '1|admin_access_token_sample...'
                        ]
                    ],
                    '401' => ['success' => false, 'message' => 'The provided credentials are incorrect.'],
                    '403' => ['success' => false, 'message' => 'Unauthorized access. Admin privileges required.'],
                    '422' => ['success' => false, 'message' => 'The given data was invalid.', 'errors' => ['login' => ['The login field is required.']]],
                    '500' => ['success' => false, 'message' => 'Internal server error.']
                ]
            ];
        }

        if (str_contains($uri, 'admin/logout')) {
            return [
                'title' => 'Admin Logout',
                'description' => 'Revoke active Admin API token and terminate session.',
                'params' => [],
                'requestExample' => null,
                'responses' => [
                    '200' => ['success' => true, 'message' => 'Admin logged out successfully.'],
                    '401' => ['success' => false, 'message' => 'Unauthenticated.'],
                    '500' => ['success' => false, 'message' => 'Internal server error.']
                ]
            ];
        }

        if (str_contains($uri, 'admin/dashboard')) {
            return [
                'title' => 'Admin Dashboard Overview',
                'description' => 'Fetch Admin metrics, platform statistics, user totals, and active sessions.',
                'params' => [],
                'requestExample' => null,
                'responses' => [
                    '200' => [
                        'success' => true,
                        'message' => 'Welcome to the Admin Dashboard',
                        'data' => [
                            'user' => ['id' => 1, 'name' => 'Super Admin', 'email' => 'admin@brags.co.uk', 'role' => 'Admin'],
                            'stats' => ['total_users' => 1450, 'active_vendors' => 85, 'total_orders' => 3200, 'revenue_gbp' => 124500.50]
                        ]
                    ],
                    '401' => ['success' => false, 'message' => 'Unauthenticated.'],
                    '403' => ['success' => false, 'message' => 'Unauthorized access. Admin privileges required.']
                ]
            ];
        }

        if (str_contains($uri, 'admin/brands')) {
            return [
                'title' => 'Register & Approve Brand',
                'description' => 'Create and approve a brand business account directly from the Admin control panel.',
                'params' => [
                    ['name' => 'brand_name', 'type' => 'string', 'required' => true, 'desc' => 'Official Brand Name'],
                    ['name' => 'business_name', 'type' => 'string', 'required' => true, 'desc' => 'Registered Business Name'],
                    ['name' => 'business_address', 'type' => 'string', 'required' => true, 'desc' => 'Physical Business Address'],
                    ['name' => 'business_contact_email', 'type' => 'string', 'required' => true, 'desc' => 'Contact Email'],
                    ['name' => 'primary_contact_name', 'type' => 'string', 'required' => true, 'desc' => 'Primary Contact Person'],
                    ['name' => 'password', 'type' => 'string', 'required' => true, 'desc' => 'Account Password'],
                    ['name' => 'trademark_office', 'type' => 'string', 'required' => true, 'desc' => 'Trademark Office (e.g. UKIPO)'],
                    ['name' => 'trademark_registration_number', 'type' => 'string', 'required' => true, 'desc' => 'Registration Number']
                ],
                'requestExample' => [
                    'brand_name' => 'Nike UK',
                    'business_name' => 'Nike UK Ltd',
                    'business_address' => '1 Sports Way, London',
                    'business_contact_email' => 'brand@nike.com',
                    'primary_contact_name' => 'Sarah Smith',
                    'password' => 'Password123!',
                    'trademark_office' => 'UKIPO',
                    'trademark_registration_number' => 'UK0000345678',
                    'brand_description' => 'Premier Athletic Footwear',
                    'manufacturing_locations' => 'UK, Vietnam',
                    'distribution_channels' => 'Online',
                    'product_categories' => 'Footwear',
                    'sell_under_own_brand' => true,
                    'approve_resellers' => true
                ],
                'responses' => [
                    '201' => [
                        'success' => true,
                        'message' => 'Brand registered successfully.',
                        'data' => [
                            'user' => ['id' => 5, 'name' => 'Sarah Smith', 'email' => 'brand@nike.com'],
                            'brand' => ['id' => 2, 'brand_name' => 'Nike UK', 'business_name' => 'Nike UK Ltd']
                        ]
                    ],
                    '422' => ['success' => false, 'message' => 'Validation Error', 'errors' => ['business_contact_email' => ['The business contact email has already been taken.']]]
                ]
            ];
        }

        if (str_contains($uri, 'admin/categories')) {
            if ($method === 'GET' && !str_contains($uri, '{')) {
                return [
                    'title' => 'List All Categories',
                    'description' => 'Retrieve catalog category hierarchy with parent and subcategory relations.',
                    'params' => [],
                    'requestExample' => null,
                    'responses' => [
                        '200' => [
                            ['id' => 1, 'name' => 'Electronics', 'slug' => 'electronics', 'parent_id' => null, 'thumbnail' => 'categories/electronics.png', 'parent' => null],
                            ['id' => 2, 'name' => 'Smartphones', 'slug' => 'smartphones', 'parent_id' => 1, 'thumbnail' => 'categories/smartphones.png', 'parent' => ['id' => 1, 'name' => 'Electronics']]
                        ]
                    ]
                ];
            }
            if ($method === 'POST') {
                return [
                    'title' => 'Create Category',
                    'description' => 'Store a new catalog category with optional parent ID and thumbnail image.',
                    'params' => [
                        ['name' => 'name', 'type' => 'string', 'required' => true, 'desc' => 'Category Name'],
                        ['name' => 'slug', 'type' => 'string', 'required' => false, 'desc' => 'URL slug (auto-generated if empty)'],
                        ['name' => 'parent_id', 'type' => 'integer', 'required' => false, 'desc' => 'Parent Category ID'],
                        ['name' => 'thumbnail', 'type' => 'file', 'required' => false, 'desc' => 'Thumbnail Image File']
                    ],
                    'requestExample' => ['name' => 'Laptops & Computers', 'slug' => 'laptops-computers', 'parent_id' => 1],
                    'responses' => [
                        '201' => ['id' => 3, 'name' => 'Laptops & Computers', 'slug' => 'laptops-computers', 'parent_id' => 1, 'thumbnail' => 'categories/laptops.png', 'created_at' => date('Y-m-d H:i:s')],
                        '422' => ['message' => 'The given data was invalid.', 'errors' => ['name' => ['The category name field is required.']]]
                    ]
                ];
            }
            if (str_contains($uri, '{')) {
                if ($method === 'GET') {
                    return [
                        'title' => 'Get Category Details',
                        'description' => 'Display specific category details by ID with parent and child subcategories.',
                        'params' => [['name' => 'category', 'type' => 'integer path', 'required' => true, 'desc' => 'Category ID']],
                        'requestExample' => null,
                        'responses' => [
                            '200' => ['id' => 1, 'name' => 'Electronics', 'slug' => 'electronics', 'parent' => null, 'children' => [['id' => 2, 'name' => 'Smartphones']]],
                            '404' => ['message' => 'Category not found.']
                        ]
                    ];
                }
                if ($method === 'PUT' || $method === 'PATCH') {
                    return [
                        'title' => 'Update Category',
                        'description' => 'Update category name, slug, or thumbnail image.',
                        'params' => [
                            ['name' => 'category', 'type' => 'integer path', 'required' => true, 'desc' => 'Category ID'],
                            ['name' => 'name', 'type' => 'string', 'required' => false, 'desc' => 'Updated Name'],
                            ['name' => 'slug', 'type' => 'string', 'required' => false, 'desc' => 'Updated Slug']
                        ],
                        'requestExample' => ['name' => 'Consumer Electronics & Tech', 'slug' => 'consumer-electronics-tech'],
                        'responses' => [
                            '200' => ['id' => 1, 'name' => 'Consumer Electronics & Tech', 'slug' => 'consumer-electronics-tech', 'updated_at' => date('Y-m-d H:i:s')],
                            '404' => ['message' => 'Category not found.']
                        ]
                    ];
                }
                if ($method === 'DELETE') {
                    return [
                        'title' => 'Delete Category',
                        'description' => 'Delete a category and remove associated storage files.',
                        'params' => [['name' => 'category', 'type' => 'integer path', 'required' => true, 'desc' => 'Category ID']],
                        'requestExample' => null,
                        'responses' => [
                            '200' => ['message' => 'Category deleted successfully'],
                            '404' => ['message' => 'Category not found.']
                        ]
                    ];
                }
            }
        }

        if (str_contains($uri, 'admin/settings/smtp')) {
            return [
                'title' => $method === 'GET' ? 'Get SMTP Settings' : 'Update SMTP Settings',
                'description' => $method === 'GET' ? 'Fetch system SMTP mail server settings.' : 'Update global system SMTP mail configuration.',
                'params' => $method === 'POST' ? [
                    ['name' => 'smtp_host', 'type' => 'string', 'required' => true, 'desc' => 'SMTP Host Server'],
                    ['name' => 'smtp_port', 'type' => 'string', 'required' => true, 'desc' => 'Port (587, 465)'],
                    ['name' => 'smtp_username', 'type' => 'string', 'required' => true, 'desc' => 'SMTP User'],
                    ['name' => 'smtp_password', 'type' => 'string', 'required' => true, 'desc' => 'SMTP Password'],
                    ['name' => 'smtp_encryption', 'type' => 'string', 'required' => true, 'desc' => 'tls or ssl'],
                    ['name' => 'smtp_from_address', 'type' => 'string', 'required' => true, 'desc' => 'Sender Email Address'],
                    ['name' => 'smtp_from_name', 'type' => 'string', 'required' => true, 'desc' => 'Sender Name']
                ] : [],
                'requestExample' => $method === 'POST' ? [
                    'smtp_host' => 'smtp.mailgun.org',
                    'smtp_port' => '587',
                    'smtp_username' => 'postmaster@brags.co.uk',
                    'smtp_password' => 'secret_password',
                    'smtp_encryption' => 'tls',
                    'smtp_from_address' => 'noreply@brags.co.uk',
                    'smtp_from_name' => 'BRAGS Platform'
                ] : null,
                'responses' => [
                    '200' => [
                        'success' => true,
                        'message' => $method === 'GET' ? 'SMTP settings fetched successfully.' : 'SMTP settings updated successfully.',
                        'data' => [
                            'smtp_host' => 'smtp.mailgun.org',
                            'smtp_port' => '587',
                            'smtp_username' => 'postmaster@brags.co.uk',
                            'smtp_encryption' => 'tls',
                            'smtp_from_address' => 'noreply@brags.co.uk',
                            'smtp_from_name' => 'BRAGS Platform'
                        ]
                    ]
                ]
            ];
        }

        if (str_contains($uri, 'admin/settings/twilio')) {
            return [
                'title' => $method === 'GET' ? 'Get Twilio Settings' : 'Update Twilio Settings',
                'description' => $method === 'GET' ? 'Fetch Twilio SMS Gateway settings.' : 'Update Twilio SID, Auth Token, and sender phone number.',
                'params' => $method === 'POST' ? [
                    ['name' => 'twilio_sid', 'type' => 'string', 'required' => true, 'desc' => 'Twilio Account SID'],
                    ['name' => 'twilio_auth_token', 'type' => 'string', 'required' => true, 'desc' => 'Twilio Auth Token'],
                    ['name' => 'twilio_from_number', 'type' => 'string', 'required' => true, 'desc' => 'Twilio Phone Number']
                ] : [],
                'requestExample' => $method === 'POST' ? [
                    'twilio_sid' => 'AC1234567890ABCDEF',
                    'twilio_auth_token' => 'secret_auth_token_key',
                    'twilio_from_number' => '+1888999000'
                ] : null,
                'responses' => [
                    '200' => [
                        'success' => true,
                        'message' => $method === 'GET' ? 'Twilio settings fetched successfully.' : 'Twilio settings updated successfully.',
                        'data' => [
                            'twilio_sid' => 'AC1234567890ABCDEF',
                            'twilio_from_number' => '+1888999000'
                        ]
                    ]
                ]
            ];
        }

        if (str_contains($uri, 'admin/settings/payment')) {
            return [
                'title' => $method === 'GET' ? 'Get Payment Settings' : 'Update Payment Settings',
                'description' => $method === 'GET' ? 'Fetch Stripe payment gateway credentials.' : 'Update Stripe API keys and webhook secret.',
                'params' => $method === 'POST' ? [
                    ['name' => 'stripe_key', 'type' => 'string', 'required' => true, 'desc' => 'Stripe Publishable Key'],
                    ['name' => 'stripe_secret', 'type' => 'string', 'required' => true, 'desc' => 'Stripe Secret Key'],
                    ['name' => 'stripe_webhook_secret', 'type' => 'string', 'required' => false, 'desc' => 'Stripe Webhook Secret']
                ] : [],
                'requestExample' => $method === 'POST' ? [
                    'stripe_key' => 'pk_live_987654321',
                    'stripe_secret' => 'sk_live_123456789',
                    'stripe_webhook_secret' => 'whsec_live_98765'
                ] : null,
                'responses' => [
                    '200' => [
                        'success' => true,
                        'message' => $method === 'GET' ? 'Payment settings fetched successfully.' : 'Payment settings updated successfully.',
                        'data' => [
                            'stripe_key' => 'pk_live_987654321',
                            'stripe_webhook_secret' => 'whsec_live_98765'
                        ]
                    ]
                ]
            ];
        }

        // ----------------------------------------------------
        // SELLER ENDPOINTS
        // ----------------------------------------------------
        if (str_contains($uri, 'seller/login')) {
            return [
                'title' => 'Seller Login',
                'description' => 'Authenticate Vendor/Seller account and issue Vendor Bearer access token.',
                'params' => [
                    ['name' => 'login', 'type' => 'string', 'required' => true, 'desc' => 'Seller Email or Username'],
                    ['name' => 'password', 'type' => 'string', 'required' => true, 'desc' => 'Password'],
                    ['name' => 'fcm_token', 'type' => 'string', 'required' => false, 'desc' => 'Firebase Cloud Messaging (FCM) device push notification token']
                ],
                'requestExample' => [
                    'login' => 'vendor@store.co.uk',
                    'password' => 'VendorPassword123!',
                    'fcm_token' => 'fcm_token_sample_device_vendor_987654321'
                ],
                'responses' => [
                    '200' => [
                        'success' => true,
                        'message' => 'Seller logged in successfully.',
                        'data' => [
                            'user' => ['id' => 12, 'name' => 'Fashion Vendor Store', 'email' => 'vendor@store.co.uk', 'roles' => [['name' => 'Seller']]],
                            'token' => '2|seller_access_token_sample...'
                        ]
                    ],
                    '401' => ['success' => false, 'message' => 'The provided credentials are incorrect.'],
                    '403' => ['success' => false, 'message' => 'Unauthorized access. Seller privileges required.']
                ]
            ];
        }

        if (str_contains($uri, 'seller/logout')) {
            return [
                'title' => 'Seller Logout',
                'description' => 'Revoke Seller API access token and end session.',
                'params' => [],
                'requestExample' => null,
                'responses' => [
                    '200' => ['success' => true, 'message' => 'Seller logged out successfully.'],
                    '401' => ['success' => false, 'message' => 'Unauthenticated.']
                ]
            ];
        }

        if (str_contains($uri, 'seller/dashboard')) {
            return [
                'title' => 'Seller Dashboard Overview',
                'description' => 'Retrieve Vendor store metrics, pending order counts, and revenue totals.',
                'params' => [],
                'requestExample' => null,
                'responses' => [
                    '200' => [
                        'success' => true,
                        'message' => 'Welcome to the Seller Dashboard',
                        'data' => [
                            'user' => ['id' => 12, 'name' => 'Fashion Vendor Store', 'email' => 'vendor@store.co.uk', 'role' => 'Seller'],
                            'store_stats' => ['store_name' => 'Fashion Vendor Store', 'pending_orders' => 14, 'processing_orders' => 8, 'total_sales_gbp' => 4580.00]
                        ]
                    ],
                    '401' => ['success' => false, 'message' => 'Unauthenticated.'],
                    '403' => ['success' => false, 'message' => 'Unauthorized access. Requires Seller role.']
                ]
            ];
        }

        if (str_contains($uri, 'seller/calculator/sbs')) {
            return [
                'title' => 'Seller SBS Profit Calculator',
                'description' => 'Calculate Shipped By Seller (SBS) profit margins, platform commission, shipping costs, and net payouts.',
                'params' => [
                    ['name' => 'product_price', 'type' => 'numeric', 'required' => true, 'desc' => 'Base Selling Price'],
                    ['name' => 'manufacturing_cost', 'type' => 'numeric', 'required' => true, 'desc' => 'Manufacturing / Cost Price'],
                    ['name' => 'shipping_cost', 'type' => 'numeric', 'required' => true, 'desc' => 'Shipping Cost to Customer'],
                    ['name' => 'vat_percent', 'type' => 'numeric', 'required' => true, 'desc' => 'VAT Percentage (e.g. 20)'],
                    ['name' => 'monthly_sales', 'type' => 'integer', 'required' => true, 'desc' => 'Total Monthly Units Sold'],
                    ['name' => 'monthly_returns', 'type' => 'integer', 'required' => true, 'desc' => 'Estimated Monthly Unit Returns'],
                    ['name' => 'include_braggers_fee', 'type' => 'boolean', 'required' => false, 'desc' => 'Include 5% affiliate fee']
                ],
                'requestExample' => [
                    'product_price' => 100.00,
                    'manufacturing_cost' => 30.00,
                    'shipping_cost' => 5.00,
                    'vat_percent' => 20,
                    'monthly_sales' => 50,
                    'monthly_returns' => 2,
                    'include_braggers_fee' => true
                ],
                'responses' => [
                    '200' => [
                        'per_product' => [
                            'brags_seller_fee' => '10.00',
                            'braggers_fee' => '5.00',
                            'ship_to_customer_cost' => '5.00',
                            'net_profit' => '30.00',
                            'net_margin_percent' => '30.00'
                        ],
                        'monthly' => [
                            'brags_seller_fee' => '500.00',
                            'braggers_fee' => '250.00',
                            'shipping_costs' => '250.00',
                            'net_profit' => '1340.00',
                            'net_margin_percent' => '27.92'
                        ]
                    ]
                ]
            ];
        }

        // ----------------------------------------------------
        // CUSTOMER ENDPOINTS
        // ----------------------------------------------------
        if (str_contains($uri, 'customer/login')) {
            return [
                'title' => 'Customer Login',
                'description' => 'Authenticate Customer account via email/username and password.',
                'params' => [
                    ['name' => 'login', 'type' => 'string', 'required' => true, 'desc' => 'Customer Email or Username'],
                    ['name' => 'password', 'type' => 'string', 'required' => true, 'desc' => 'Password'],
                    ['name' => 'fcm_token', 'type' => 'string', 'required' => false, 'desc' => 'Firebase Cloud Messaging (FCM) device push notification token']
                ],
                'requestExample' => [
                    'login' => 'customer@gmail.com',
                    'password' => 'CustomerPassword123!',
                    'fcm_token' => 'fcm_token_sample_device_customer_123456789'
                ],
                'responses' => [
                    '200' => [
                        'success' => true,
                        'message' => 'Customer logged in successfully.',
                        'data' => [
                            'user' => ['id' => 25, 'name' => 'Alice Customer', 'email' => 'customer@gmail.com', 'roles' => [['name' => 'Customer']]],
                            'token' => '3|customer_access_token_sample...'
                        ]
                    ],
                    '401' => ['success' => false, 'message' => 'The provided credentials are incorrect.']
                ]
            ];
        }

        if (str_contains($uri, 'customer/logout')) {
            return [
                'title' => 'Customer Logout',
                'description' => 'Revoke Customer API access token.',
                'params' => [],
                'requestExample' => null,
                'responses' => [
                    '200' => ['success' => true, 'message' => 'Customer logged out successfully.'],
                    '401' => ['success' => false, 'message' => 'Unauthenticated.']
                ]
            ];
        }

        if (str_contains($uri, 'customer/dashboard')) {
            return [
                'title' => 'Customer Dashboard Summary',
                'description' => 'Retrieve Customer account profile overview, active order status, and wishlist count.',
                'params' => [],
                'requestExample' => null,
                'responses' => [
                    '200' => [
                        'success' => true,
                        'message' => 'Welcome to the Customer Dashboard',
                        'data' => [
                            'user' => ['id' => 25, 'name' => 'Alice Customer', 'email' => 'customer@gmail.com', 'role' => 'Customer'],
                            'summary' => ['active_orders' => 2, 'completed_orders' => 12, 'wishlist_count' => 5]
                        ]
                    ],
                    '401' => ['success' => false, 'message' => 'Unauthenticated.']
                ]
            ];
        }

        // ----------------------------------------------------
        // GENERAL / AUTH / BRAND / BRAGGER ENDPOINTS (v1)
        // ----------------------------------------------------
        if (str_contains($uri, 'login/otp/send')) {
            return [
                'title' => 'Send Login OTP',
                'description' => 'Send a 6-digit OTP code to registered mobile phone for passwordless login.',
                'params' => [['name' => 'phone', 'type' => 'string', 'required' => true, 'desc' => 'Phone number with country code']],
                'requestExample' => ['phone' => '+447123456789'],
                'responses' => [
                    '200' => [
                        'success' => true,
                        'message' => 'Login OTP sent successfully.',
                        'data' => ['otp_sent' => true, 'expires_in_seconds' => 300]
                    ],
                    '400' => ['success' => false, 'message' => 'Phone number not registered.']
                ]
            ];
        }

        if (str_contains($uri, 'login/otp/verify')) {
            return [
                'title' => 'Verify Login OTP',
                'description' => 'Verify 6-digit OTP code and authenticate user session.',
                'params' => [
                    ['name' => 'phone', 'type' => 'string', 'required' => true, 'desc' => 'Mobile phone number'],
                    ['name' => 'otp', 'type' => 'string', 'required' => true, 'desc' => '6-digit OTP code']
                ],
                'requestExample' => ['phone' => '+447123456789', 'otp' => '123456'],
                'responses' => [
                    '200' => [
                        'success' => true,
                        'message' => 'User logged in successfully with OTP.',
                        'data' => [
                            'user' => ['id' => 1, 'phone' => '+447123456789'],
                            'token' => '5|otp_access_token_sample...'
                        ]
                    ],
                    '422' => ['success' => false, 'message' => 'Invalid or expired OTP code.']
                ]
            ];
        }

        if (str_contains($uri, 'forgot-password')) {
            return [
                'title' => 'Forgot Password OTP Request',
                'description' => 'Request password reset OTP code sent to user registered email.',
                'params' => [['name' => 'email', 'type' => 'string', 'required' => true, 'desc' => 'Registered Email Address']],
                'requestExample' => ['email' => 'user@example.com'],
                'responses' => [
                    '200' => [
                        'success' => true,
                        'message' => 'OTP sent successfully.',
                        'data' => ['email' => 'user@example.com']
                    ]
                ]
            ];
        }

        if (str_contains($uri, 'verify-otp')) {
            return [
                'title' => 'Verify Password Reset OTP',
                'description' => 'Verify password reset OTP code before setting new password.',
                'params' => [
                    ['name' => 'email', 'type' => 'string', 'required' => true, 'desc' => 'Email Address'],
                    ['name' => 'otp', 'type' => 'string', 'required' => true, 'desc' => '6-digit OTP code']
                ],
                'requestExample' => ['email' => 'user@example.com', 'otp' => '654321'],
                'responses' => [
                    '200' => ['success' => true, 'message' => 'OTP verified successfully.'],
                    '422' => ['success' => false, 'message' => 'Invalid or expired OTP code.']
                ]
            ];
        }

        if (str_contains($uri, 'reset-password')) {
            return [
                'title' => 'Reset Password',
                'description' => 'Set new password after OTP verification.',
                'params' => [
                    ['name' => 'email', 'type' => 'string', 'required' => true, 'desc' => 'User Email'],
                    ['name' => 'otp', 'type' => 'string', 'required' => true, 'desc' => 'Verified OTP Code'],
                    ['name' => 'password', 'type' => 'string', 'required' => true, 'desc' => 'New Password'],
                    ['name' => 'password_confirmation', 'type' => 'string', 'required' => true, 'desc' => 'Confirm Password']
                ],
                'requestExample' => [
                    'email' => 'user@example.com',
                    'otp' => '654321',
                    'password' => 'newpassword123',
                    'password_confirmation' => 'newpassword123'
                ],
                'responses' => [
                    '200' => ['success' => true, 'message' => 'Password reset successfully.']
                ]
            ];
        }

        if (str_contains($uri, 'v1/register')) {
            return [
                'title' => 'User Self Registration',
                'description' => 'Create a new user account on the platform.',
                'params' => [
                    ['name' => 'name', 'type' => 'string', 'required' => true, 'desc' => 'Full Name'],
                    ['name' => 'email', 'type' => 'string', 'required' => true, 'desc' => 'Unique Email Address'],
                    ['name' => 'password', 'type' => 'string', 'required' => true, 'desc' => 'Password (min 8 chars)'],
                    ['name' => 'password_confirmation', 'type' => 'string', 'required' => true, 'desc' => 'Confirm Password']
                ],
                'requestExample' => [
                    'name' => 'John Doe',
                    'email' => 'user@example.com',
                    'password' => 'password123',
                    'password_confirmation' => 'password123'
                ],
                'responses' => [
                    '201' => [
                        'success' => true,
                        'message' => 'User registered successfully.',
                        'data' => [
                            'user' => ['id' => 1, 'name' => 'John Doe', 'email' => 'user@example.com', 'created_at' => date('Y-m-d H:i:s')],
                            'token' => '4|user_access_token_sample...'
                        ]
                    ],
                    '422' => ['success' => false, 'message' => 'The given data was invalid.', 'errors' => ['email' => ['The email has already been taken.']]]
                ]
            ];
        }

        if (str_contains($uri, 'v1/login')) {
            return [
                'title' => 'User Login',
                'description' => 'Authenticate standard user credentials.',
                'params' => [
                    ['name' => 'login', 'type' => 'string', 'required' => true, 'desc' => 'Email or Username'],
                    ['name' => 'password', 'type' => 'string', 'required' => true, 'desc' => 'Password'],
                    ['name' => 'fcm_token', 'type' => 'string', 'required' => false, 'desc' => 'Firebase Cloud Messaging (FCM) device push notification token']
                ],
                'requestExample' => [
                    'login' => 'user@example.com',
                    'password' => 'password123',
                    'fcm_token' => 'fcm_token_sample_device_user_445566'
                ],
                'responses' => [
                    '200' => [
                        'success' => true,
                        'message' => 'User logged in successfully.',
                        'data' => [
                            'user' => ['id' => 1, 'name' => 'John Doe', 'email' => 'user@example.com'],
                            'token' => '4|user_access_token_sample...'
                        ]
                    ],
                    '401' => ['success' => false, 'message' => 'The provided credentials are incorrect.']
                ]
            ];
        }

        if (str_contains($uri, 'v1/logout')) {
            return [
                'title' => 'User Logout',
                'description' => 'Revoke current user token and terminate session.',
                'params' => [],
                'requestExample' => null,
                'responses' => [
                    '200' => ['success' => true, 'message' => 'User logged out successfully.']
                ]
            ];
        }

        if (str_contains($uri, 'v1/profile')) {
            if ($method === 'GET') {
                return [
                    'title' => 'Get User Profile',
                    'description' => 'Fetch authenticated user details, contact info, and assigned roles.',
                    'params' => [],
                    'requestExample' => null,
                    'responses' => [
                        '200' => [
                            'success' => true,
                            'message' => 'Profile fetched successfully.',
                            'data' => [
                                'id' => 1,
                                'name' => 'John Doe',
                                'email' => 'user@example.com',
                                'detail' => ['phone' => '+447123456789', 'address' => '123 Main St, London'],
                                'roles' => [['name' => 'Customer']]
                            ]
                        ]
                    ]
                ];
            }
            if ($method === 'PUT') {
                return [
                    'title' => 'Update User Profile',
                    'description' => 'Update user basic info, phone number, and detail attributes.',
                    'params' => [
                        ['name' => 'name', 'type' => 'string', 'required' => false, 'desc' => 'Full Name'],
                        ['name' => 'phone', 'type' => 'string', 'required' => false, 'desc' => 'Contact Number'],
                        ['name' => 'address', 'type' => 'string', 'required' => false, 'desc' => 'Physical Address']
                    ],
                    'requestExample' => [
                        'name' => 'Johnathan Doe',
                        'phone' => '+447999888777',
                        'address' => '456 Oxford St, London'
                    ],
                    'responses' => [
                        '200' => [
                            'success' => true,
                            'message' => 'Profile updated successfully.',
                            'data' => [
                                'id' => 1,
                                'name' => 'Johnathan Doe',
                                'detail' => ['phone' => '+447999888777', 'address' => '456 Oxford St, London']
                            ]
                        ]
                    ]
                ];
            }
        }

        if (str_contains($uri, 'change-password')) {
            return [
                'title' => 'Change Account Password',
                'description' => 'Change current user account password.',
                'params' => [
                    ['name' => 'current_password', 'type' => 'string', 'required' => true, 'desc' => 'Old Password'],
                    ['name' => 'password', 'type' => 'string', 'required' => true, 'desc' => 'New Password'],
                    ['name' => 'password_confirmation', 'type' => 'string', 'required' => true, 'desc' => 'Confirm New Password']
                ],
                'requestExample' => [
                    'current_password' => 'oldpassword123',
                    'password' => 'newpassword123',
                    'password_confirmation' => 'newpassword123'
                ],
                'responses' => [
                    '200' => ['success' => true, 'message' => 'Password changed successfully.'],
                    '422' => ['success' => false, 'message' => 'The given data was invalid.', 'errors' => ['current_password' => ['The current password is incorrect.']]]
                ]
            ];
        }

        if (str_contains($uri, 'v1/brand/register')) {
            return [
                'title' => 'Brand Self Registration',
                'description' => 'Register a new Brand merchant profile with trademark details, logo, and product list.',
                'params' => [
                    ['name' => 'brand_name', 'type' => 'string', 'required' => true, 'desc' => 'Brand Name'],
                    ['name' => 'business_name', 'type' => 'string', 'required' => true, 'desc' => 'Business Name'],
                    ['name' => 'business_contact_email', 'type' => 'string', 'required' => true, 'desc' => 'Contact Email'],
                    ['name' => 'primary_contact_name', 'type' => 'string', 'required' => true, 'desc' => 'Primary Contact Person'],
                    ['name' => 'password', 'type' => 'string', 'required' => true, 'desc' => 'Password'],
                    ['name' => 'trademark_office', 'type' => 'string', 'required' => true, 'desc' => 'Trademark Office'],
                    ['name' => 'trademark_registration_number', 'type' => 'string', 'required' => true, 'desc' => 'Trademark Registration #'],
                    ['name' => 'brand_logo', 'type' => 'file', 'required' => true, 'desc' => 'Logo Image File'],
                    ['name' => 'products[0][identifier]', 'type' => 'string', 'required' => true, 'desc' => 'Product SKU/Identifier'],
                    ['name' => 'products[0][image]', 'type' => 'file', 'required' => true, 'desc' => 'Product Image File']
                ],
                'requestExample' => [
                    'brand_name' => 'Nike UK',
                    'business_name' => 'Nike UK Ltd',
                    'business_address' => '1 Sports Way, London',
                    'business_contact_email' => 'brand@nike.com',
                    'primary_contact_name' => 'Sarah Smith',
                    'password' => 'Password123!',
                    'password_confirmation' => 'Password123!',
                    'trademark_office' => 'UKIPO',
                    'trademark_registration_number' => 'UK0000345678',
                    'brand_description' => 'Premier Athletic Footwear',
                    'manufacturing_locations' => 'UK, Vietnam',
                    'distribution_channels' => 'Online',
                    'product_categories' => 'Footwear',
                    'sell_under_own_brand' => true,
                    'approve_resellers' => true,
                    'products' => [['identifier' => 'AIR-MAX-90', 'image' => '(binary file)']]
                ],
                'responses' => [
                    '201' => [
                        'success' => true,
                        'message' => 'Brand registered successfully.',
                        'data' => [
                            'user' => ['id' => 5, 'name' => 'Sarah Smith', 'email' => 'brand@nike.com'],
                            'brand' => ['id' => 2, 'brand_name' => 'Nike UK', 'registrationProducts' => [['id' => 10, 'product_identifier' => 'AIR-MAX-90']]]
                        ]
                    ],
                    '422' => ['success' => false, 'message' => 'Registration failed.', 'errors' => ['business_contact_email' => ['Email already registered.']]]
                ]
            ];
        }

        if (str_contains($uri, 'v1/bragger/register')) {
            return [
                'title' => 'Bragger (Affiliate) Registration',
                'description' => 'Register a new Bragger influencer affiliate account.',
                'params' => [
                    ['name' => 'first_name', 'type' => 'string', 'required' => true, 'desc' => 'First Name'],
                    ['name' => 'last_name', 'type' => 'string', 'required' => true, 'desc' => 'Last Name'],
                    ['name' => 'username', 'type' => 'string', 'required' => true, 'desc' => 'Social Handles / Username'],
                    ['name' => 'email', 'type' => 'string', 'required' => true, 'desc' => 'Email Address'],
                    ['name' => 'password', 'type' => 'string', 'required' => true, 'desc' => 'Account Password'],
                    ['name' => 'payment_email', 'type' => 'string', 'required' => true, 'desc' => 'Payout PayPal/Bank Email'],
                    ['name' => 'accept_terms', 'type' => 'boolean', 'required' => true, 'desc' => 'Accept Terms & Conditions']
                ],
                'requestExample' => [
                    'first_name' => 'Emily',
                    'last_name' => 'Watson',
                    'username' => 'emily_creator',
                    'email' => 'emily@influencer.com',
                    'password' => 'Password123!',
                    'payment_email' => 'paypal@emily.com',
                    'promote_method' => 'Instagram, TikTok',
                    'accept_terms' => true
                ],
                'responses' => [
                    '201' => [
                        'message' => 'Bragger registered successfully.',
                        'user' => ['id' => 8, 'name' => 'Emily Watson', 'username' => 'emily_creator', 'email' => 'emily@influencer.com'],
                        'token' => '6|bragger_access_token_sample...'
                    ],
                    '500' => ['message' => 'Registration failed.', 'error' => 'Database exception']
                ]
            ];
        }

        if (str_contains($uri, 'v1/bragger/calculator')) {
            return [
                'title' => 'Bragger Affiliate Payout Calculator',
                'description' => 'Calculate estimated affiliate commission income based on product price, sales volume, and return rate.',
                'params' => [
                    ['name' => 'product_price', 'type' => 'numeric', 'required' => true, 'desc' => 'Product Selling Price'],
                    ['name' => 'monthly_sales', 'type' => 'integer', 'required' => true, 'desc' => 'Estimated Monthly Sales Count'],
                    ['name' => 'monthly_returns', 'type' => 'integer', 'required' => true, 'desc' => 'Estimated Return Units']
                ],
                'requestExample' => [
                    'product_price' => 500.00,
                    'monthly_sales' => 20,
                    'monthly_returns' => 1
                ],
                'responses' => [
                    '200' => [
                        'inputs' => ['product_price' => '500.00', 'monthly_sales' => 20, 'monthly_returns' => 1, 'commission_rate' => 0.05],
                        'results' => ['commission_per_item' => '25.00', 'total_monthly_income' => '500.00', 'income_after_returns' => '475.00']
                    ]
                ]
            ];
        }

        // Default Fallback for any newly added route
        return [
            'title' => ucfirst($role) . ' - ' . strtoupper($method) . ' ' . str_replace(['api/', 'v1/', '/'], [' ', ' ', ' '], $uri),
            'description' => ucfirst($role) . ' API endpoint for /' . $uri . '.',
            'params' => [],
            'requestExample' => null,
            'responses' => [
                '200' => ['success' => true, 'message' => 'Request successful.']
            ]
        ];
    }
}
